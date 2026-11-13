<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\CampaignPeriod;
use App\Models\DoctoralEnrolment;
use App\Models\ResearchSubject;
use App\Models\Renewal;
use App\Models\User;
use App\Services\ApplicationService;
use Database\Seeders\EstfDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RenewalTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith([true])]
    #[TestWith([false])]
    public function test_renewal_progresses_from_an_enrolled_candidate_to_supervisor_and_administration(bool $extensionApproved): void
    {
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        $subject = ResearchSubject::firstOrFail();
        $candidate = User::factory()->create(['is_active' => true]);
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $other = User::factory()->professor()->create(['is_active' => true]);
        $application = app(ApplicationService::class)->submit($candidate, $subject, null, UploadedFile::fake()->create('lettre.pdf'));

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->post(route('enrolments.confirm', $application), ['verified' => 1])->assertUnprocessable();
        $application->update(['status' => ApplicationStatus::Accepted]);
        $this->get(route('admin.applications.show', $application))->assertOk()->assertSee('Contrôler les pièces administratives');
        $manager = User::factory()->create(['role' => \App\Enums\UserRole::Staff, 'is_active' => true]);
        $this->actingAs($manager)->post(route('enrolments.confirm', $application), ['verified' => 1])->assertForbidden();
        $manager->staffAssignments()->create(['research_subject_id' => $subject->id, 'function' => \App\Enums\StaffFunction::Manager]);
        $managerQueue = route('staff.workspace', ['fonction' => 'manager', 'a_traiter' => 1]);
        $this->get($managerQueue)->assertOk()->assertViewHas('actionCount', 0);
        $this->get(route('staff.application', $application))->assertOk()->assertSee('Contrôler les pièces administratives');
        $this->post(route('enrolments.confirm', $application), ['verified' => 1])->assertSessionHasErrors('verified');
        $this->actingAs($candidate)->get(route('candidate.dashboard'))->assertOk()
            ->assertSee('Compléter mes pièces administratives')->assertSee('À déposer')
            ->assertSee(route('enrolment-documents.show', $application));
        foreach (array_keys(\App\Models\EnrolmentDocument::REQUIRED) as $kind) {
            $upload = route('enrolment-documents.upload', [$application, $kind]);
            $this->actingAs($candidate)->post($upload, ['document' => UploadedFile::fake()->create('piece.pdf', 10, 'application/pdf')])->assertRedirect();
            $document = $application->enrolmentDocuments()->where('kind', $kind)->firstOrFail();
            $review = route('enrolment-documents.review', [$application, $document]);
            $this->actingAs($manager);
            if ($kind === 'identity') {
                $this->get($managerQueue)->assertOk()->assertViewHas('actionCount', 1);
                $version = hash('sha256', $document->path);
                $this->patch($review, ['status' => 'correction', 'version' => $version])->assertSessionHasErrors('comment');
                $this->patch($review, ['status' => 'correction', 'version' => $version, 'comment' => 'Pièce illisible.'])->assertRedirect();
                $this->actingAs($candidate)->get(route('enrolment-documents.show', $application))->assertOk()->assertSee('Pièce illisible.');
                $this->get(route('candidate.dashboard'))->assertOk()->assertSee('À remplacer : Pièce d’identité');
                $this->post($upload, ['document' => UploadedFile::fake()->create('remplacement.pdf', 10, 'application/pdf')])->assertRedirect();
                $this->actingAs($manager)->patch($review, ['status' => 'approved', 'version' => $version])->assertConflict();
                $document->refresh();
            }
            $this->patch($review, ['status' => 'approved', 'version' => hash('sha256', $document->path)])->assertRedirect();
        }
        $this->actingAs($other)->get(route('enrolment-documents.show', $application))->assertForbidden();
        $this->actingAs($manager)->get(route('enrolment-documents.show', $application))->assertOk()->assertSee('Confirmer l’inscription');
        $this->post(route('enrolments.confirm', $application))->assertSessionHasErrors('verified');
        $this->post(route('enrolments.confirm', $application), ['verified' => 1])->assertRedirect();
        $this->post(route('enrolments.confirm', $application), ['verified' => 1])->assertRedirect();
        $this->assertDatabaseCount('doctoral_enrolments', 1);
        $this->assertDatabaseHas('doctoral_enrolments', ['application_id' => $application->id, 'confirmed_by' => $manager->id]);
        $this->actingAs($candidate)->get(route('candidate.applications.show', $application))->assertOk()
            ->assertSee('Inscription confirmée')->assertSee('Accéder à mes réinscriptions');
        $enrolment = DoctoralEnrolment::firstOrFail();

        $this->actingAs($candidate)->get(route('renewals.index'))->assertOk()->assertSee('Bilan annuel');
        $this->get(route('candidate.dashboard'))->assertDontSee('Compléter mes pièces administratives')->assertDontSee('Déposer mon bilan annuel');
        CampaignPeriod::create([
            'academic_year' => '2027–2028',
            'phase' => 'renewal',
            'opens_on' => now('Africa/Casablanca')->subDay()->toDateString(),
            'closes_on' => now('Africa/Casablanca')->addDay()->toDateString(),
        ]);
        $renewalUrl = route('renewals.index', ['inscription' => $enrolment->id]);
        $this->get(route('candidate.dashboard'))->assertOk()->assertSee('Déposer mon bilan annuel')->assertSee($renewalUrl.'#bilan-'.$enrolment->id);
        $this->get($renewalUrl)->assertOk()->assertSee('id="bilan-'.$enrolment->id.'"', false);
        $this->actingAs(User::factory()->create(['is_active' => true]))->get($renewalUrl)->assertNotFound();
        $this->actingAs($candidate);
        $payload = ['academic_year' => '2027–2028', 'report' => 'Bilan annuel de démonstration', 'extension' => 1];
        $this->post(route('renewals.store', $enrolment), $payload + ['attachment' => UploadedFile::fake()->create('bilan.pdf', 10, 'application/pdf')])->assertRedirect(route('renewals.index'));
        $this->post(route('renewals.store', $enrolment), $payload)->assertSessionHasErrors('report');
        $renewal = Renewal::firstOrFail();
        $this->get(route('renewals.attachment', $renewal))->assertDownload('bilan-annuel.pdf');
        Storage::disk('local')->assertExists($renewal->attachment_path);
        $this->actingAs($other)->get(route('renewals.attachment', $renewal))->assertForbidden();
        $opinion = ['status' => 'favorable', 'comment' => 'Travaux conformes au programme.'];
        $this->actingAs($other)->post(route('renewals.review', $renewal), $opinion)->assertForbidden();
        $this->actingAs($subject->professor)->get(route('renewals.index'))->assertOk()->assertSee('Avis favorable');
        $this->post(route('renewals.review', $renewal), $opinion)->assertRedirect(route('renewals.index'));

        $decision = ['status' => 'confirmed', 'comment' => 'Dossier conforme et accords reçus.'];
        $this->actingAs($manager)->post(route('renewals.decide', $renewal), $decision + ['verified' => 1])->assertSessionHasErrors('verified');
        $this->post(route('renewals.approve', [$renewal, 'head']), ['approved' => 1, 'comment' => 'Accord.'])->assertForbidden();

        $this->post(route('renewals.correction', $renewal), ['reason' => 'Précisez les résultats du bilan.'])->assertRedirect();
        $notice = $candidate->notifications->firstWhere('data.title', 'Complément demandé pour votre réinscription');
        $this->assertNotNull($notice);
        $this->actingAs($candidate)->post(route('candidate.notifications.open', $notice->id))->assertRedirect(route('renewals.index'));
        $this->actingAs($manager);
        $this->post(route('renewals.decide', $renewal), $decision + ['verified' => 1])->assertConflict();
        $this->actingAs($other)->post(route('renewals.resubmit', $renewal), ['report' => 'Autre bilan', 'round' => 1])->assertForbidden();
        $this->actingAs($candidate)->get(route('renewals.index'))->assertOk()->assertSee('Précisez les résultats du bilan.');
        $this->get(route('candidate.dashboard'))->assertOk()->assertSee('Corriger ma réinscription')
            ->assertSee($renewalUrl.'#reinscription-'.$renewal->id)->assertDontSee('Déposer mon bilan annuel');
        $this->post(route('renewals.resubmit', $renewal), ['report' => $payload['report'], 'round' => 1])->assertSessionHasErrors('report');
        $this->post(route('renewals.resubmit', $renewal), [
            'report' => 'Bilan corrigé avec résultats.',
            'round' => 1,
            'attachment' => UploadedFile::fake()->create('bilan-corrige.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('renewals.index'));
        $renewal->refresh();
        $this->assertSame('submitted', $renewal->status);
        $this->get(route('candidate.dashboard'))->assertOk()->assertDontSee('Corriger ma réinscription');
        $this->assertTrue($renewal->correction_history[0]['attachment_replaced']);
        $this->assertSame($manager->id, $renewal->correction_history[0]['requested_by']);
        $this->actingAs($subject->professor)->post(route('renewals.review', $renewal), $opinion)->assertRedirect(route('renewals.index'));

        $extensionRoute = route('renewals.extension.decide', $renewal);
        $extension = ['approved' => $extensionApproved, 'comment' => 'Décision motivée sur la prolongation.', 'revision' => 1];
        $this->actingAs($manager)->post($extensionRoute, $extension)->assertForbidden();

        foreach (Renewal::APPROVALS as $function) {
            $staff = User::factory()->create(['role' => \App\Enums\UserRole::Staff, 'is_active' => true]);
            $staff->staffAssignments()->create(['research_subject_id' => $subject->id, 'function' => $function]);
            $this->actingAs($staff)->get(route('staff.workspace', ['fonction' => $function->value]))
                ->assertOk()->assertSee('Motif de votre avis');
            $this->post(route('renewals.approve', [$renewal, $function->value]), ['approved' => 1, 'comment' => 'Accord.'])->assertRedirect();
            if ($function === \App\Enums\StaffFunction::Head) {
                $head = $staff;
            }
            $this->post(route('renewals.approve', [$renewal, $function->value]), ['approved' => 0, 'comment' => 'Modification.'])->assertConflict();
            $this->assertDatabaseHas('renewal_approvals', ['renewal_id' => $renewal->id, 'user_id' => $staff->id, 'function' => $function->value, 'approved' => true]);
        }

        $this->actingAs($manager)->post(route('renewals.decide', $renewal), $decision + ['verified' => 1])
            ->assertSessionHasErrors('verified');
        $this->get($managerQueue)->assertOk()->assertViewHas('actionCount', 0);
        $headQueue = route('staff.workspace', ['fonction' => 'head', 'a_traiter' => 1]);
        $this->actingAs($head)->get($headQueue)->assertOk()->assertViewHas('actionCount', 1)->assertSee('Accorder la prolongation');
        $this->post($extensionRoute, array_replace($extension, ['revision' => 0]))->assertConflict();
        $this->post($extensionRoute, $extension)->assertRedirect(route('staff.workspace', ['fonction' => 'head']));
        $this->get($headQueue)->assertOk()->assertViewHas('actionCount', 0);
        $this->actingAs($manager)->get($managerQueue)->assertOk()->assertViewHas('actionCount', 1);
        $this->actingAs($head);
        $this->post($extensionRoute, $extension)->assertConflict();
        $this->assertDatabaseHas('renewal_approvals', [
            'renewal_id' => $renewal->id, 'function' => Renewal::EXTENSION_DECISION,
            'user_id' => $head->id, 'approved' => $extensionApproved,
        ]);
        $this->actingAs($candidate)->get(route('renewals.index'))->assertOk()
            ->assertSee($extensionApproved ? 'Prolongation accordée' : 'Prolongation refusée')
            ->assertSee($extension['comment']);

        if (! $extensionApproved) {
            $this->actingAs($manager)->post(route('renewals.decide', $renewal), $decision + ['verified' => 1])
                ->assertSessionHasErrors('verified');
            $this->post(route('renewals.decide', $renewal), ['status' => 'rejected', 'comment' => 'Prolongation refusée.'])
                ->assertRedirect(route('staff.workspace', ['fonction' => 'manager']));
            $this->actingAs($candidate)->get(route('renewals.index'))->assertOk()->assertSee('Réinscription refusée');
            return;
        }

        $this->actingAs($manager)->get(route('staff.workspace', ['fonction' => 'manager']))
            ->assertOk()->assertSee('Confirmer la réinscription');
        $this->post(route('renewals.decide', $renewal), $decision)->assertSessionHasErrors('verified');
        $this->post(route('renewals.decide', $renewal), $decision + ['verified' => 1])
            ->assertRedirect(route('staff.workspace', ['fonction' => 'manager']));
        $this->actingAs($candidate)->get(route('renewals.index'))->assertOk()->assertSee('Réinscription confirmée');
        $this->assertDatabaseHas('renewals', ['id' => $renewal->id, 'status' => 'confirmed', 'supervisor_id' => $subject->professor_id, 'administrator_id' => $manager->id]);
    }
}
