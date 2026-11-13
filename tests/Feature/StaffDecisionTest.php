<?php

namespace Tests\Feature;

use App\Enums\StaffFunction;
use App\Enums\UserRole;
use App\Models\ResearchSubject;
use App\Models\User;
use App\Services\ApplicationService;
use Database\Seeders\EstfDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffDecisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_requires_both_approvals_for_the_current_version(): void
    {
        $this->seed(EstfDemoSeeder::class);
        $subject = ResearchSubject::firstOrFail();
        $subject->update(['description' => 'Version à valider.']);
        $revision = $subject->approvalRevision();
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $this->actingAs($admin)->patch(route('admin.subjects.toggle-open', $subject))->assertSessionHasErrors('publication');
        $this->get(route('public.subjects.show', $subject))->assertNotFound();
        $staff = User::factory()->create(['role' => UserRole::Staff, 'is_active' => true]);
        $data = ['approved' => 1, 'comment' => 'Avis favorable.', 'revision' => $revision];
        $this->actingAs($staff)->post(route('staff.subject.approve', [$subject, 'lab']), $data)->assertForbidden();

        foreach (ResearchSubject::APPROVALS as $function) {
            $staff->staffAssignments()->create(['research_subject_id' => $subject->id, 'function' => $function]);
            $this->get(route('staff.workspace', ['fonction' => $function->value]))->assertOk()->assertSee('Approuver');
            $actionQueue = route('staff.workspace', ['fonction' => $function->value, 'a_traiter' => 1]);
            $this->get($actionQueue)->assertOk()->assertSee('1 élément à traiter')->assertSee($subject->title);
            $this->post(route('staff.subject.approve', [$subject, $function->value]), $data)->assertRedirect()->assertSessionHasNoErrors();
            $this->get($actionQueue)->assertOk()->assertSee('Aucune action attendue')->assertDontSee($subject->title);
        }
        $this->actingAs($admin)->patch(route('admin.subjects.toggle-open', $subject))->assertSessionHasNoErrors();
        $this->get(route('public.subjects.show', $subject))->assertOk();
        $subject->refresh()->update(['description' => 'Une autre version.']);
        $this->get(route('public.subjects.show', $subject))->assertNotFound();
        $this->actingAs($staff)->post(route('staff.subject.approve', [$subject, 'lab']), $data)->assertConflict();
    }

    public function test_commission_can_decide_only_on_assigned_applications(): void
    {
        Mail::fake();
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        $subject = ResearchSubject::firstOrFail();
        $candidate = User::factory()->create();
        $application = app(ApplicationService::class)->submit($candidate, $subject, null, UploadedFile::fake()->create('lettre.pdf'));
        $staff = User::factory()->create(['role' => UserRole::Staff, 'is_active' => true]);
        $this->actingAs($staff)->patch(route('staff.application.review', $application), ['status' => 'rejected'])->assertForbidden();

        $staff->staffAssignments()->create(['research_subject_id' => $subject->id, 'function' => StaffFunction::Selection]);
        $queue = route('staff.workspace', ['fonction' => 'selection', 'a_traiter' => 1]);
        $this->get($queue)->assertOk()->assertViewHas('actionCount', 1);
        $this->get(route('staff.application', $application))->assertOk()->assertSee('Décision de sélection');
        $this->patch(route('staff.application.review', $application), ['status' => 'under_review'])->assertSessionHasErrors('oral_exam_date');
        $this->patch(route('staff.application.review', $application), ['status' => 'rejected'])->assertSessionHasErrors('comment');
        $this->patch(route('staff.application.review', $application), ['status' => 'rejected', 'comment' => 'Critères de sélection non satisfaits.'])->assertRedirect(route('staff.application', $application));
        $this->get($queue)->assertOk()->assertViewHas('actionCount', 0);
        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'rejected', 'reviewed_by' => $staff->id]);
        $this->assertDatabaseHas('application_status_logs', ['application_id' => $application->id, 'changed_by' => $staff->id, 'to_status' => 'rejected']);
    }

    public function test_commission_can_promote_a_waitlisted_candidate_after_the_oral(): void
    {
        Mail::fake();
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        $subject = ResearchSubject::firstOrFail();
        $candidate = User::factory()->create(['is_active' => true]);
        $application = app(ApplicationService::class)->submit($candidate, $subject, null, UploadedFile::fake()->create('lettre.pdf'));
        $staff = User::factory()->create(['role' => UserRole::Staff, 'is_active' => true]);
        $staff->staffAssignments()->create(['research_subject_id' => $subject->id, 'function' => StaffFunction::Selection]);
        $decision = route('staff.application.review', $application);
        $this->actingAs($staff)->patch($decision, ['status' => 'waitlisted', 'waitlist_rank' => 2])->assertSessionHasErrors('status');

        $application->update(['status' => 'under_review', 'oral_exam_at' => now()->subDay()]);
        $this->patch($decision, ['status' => 'waitlisted', 'waitlist_rank' => 2])->assertRedirect(route('staff.application', $application));
        Mail::assertSent(\App\Mail\ApplicationWaitlisted::class, 1);
        $entry = $candidate->unreadNotifications()->sole();
        $this->assertSame($application->id, $entry->data['application_id']);
        $this->actingAs($candidate)->get(route('candidate.dashboard'))->assertOk()->assertSee('1 non lue');
        $this->get(route('candidate.notifications.index'))->assertOk()->assertSee('Sur liste d’attente');
        $this->actingAs(User::factory()->create(['is_active' => true]))
            ->post(route('candidate.notifications.open', $entry->id))->assertNotFound();
        $this->assertNull($entry->fresh()->read_at);
        $this->actingAs($candidate)->post(route('candidate.notifications.open', $entry->id))
            ->assertRedirect(route('candidate.applications.show', $application));
        $this->assertNotNull($entry->fresh()->read_at);
        $this->actingAs($staff);
        $other = app(ApplicationService::class)->submit(User::factory()->create(), $subject, null, UploadedFile::fake()->create('lettre.pdf'));
        $other->update(['status' => 'under_review', 'oral_exam_at' => now()->subDay()]);
        $otherDecision = route('staff.application.review', $other);
        $this->patch($otherDecision, ['status' => 'waitlisted'])->assertSessionHasErrors('waitlist_rank');
        $this->patch($otherDecision, ['status' => 'waitlisted', 'waitlist_rank' => 2])->assertSessionHasErrors('waitlist_rank');
        $this->patch($otherDecision, ['status' => 'waitlisted', 'waitlist_rank' => 1])->assertRedirect();
        $this->get(route('staff.workspace', ['fonction' => 'selection', 'liste_attente' => 1]))
            ->assertOk()->assertSeeInOrder(['Rang 1', 'Rang 2']);
        $this->actingAs($candidate)->get(route('candidate.applications.show', $application))
            ->assertOk()->assertSee('Votre candidature est sur liste d’attente.')->assertSee('Rang 2')->assertDontSee('Rang 1');

        $this->actingAs($staff)->patch($decision, [
            'status' => 'accepted', 'program_start_date' => now()->addDays(5)->toDateString(),
        ])->assertRedirect(route('staff.application', $application));
        Mail::assertSent(\App\Mail\ApplicationAccepted::class, 1);
        $this->assertSame(1, $candidate->unreadNotifications()->count());
        $this->actingAs($candidate)->post(route('candidate.notifications.read-all'))
            ->assertRedirect(route('candidate.notifications.index'));
        $this->assertSame(0, $candidate->unreadNotifications()->count());
        $this->assertNull($application->fresh()->waitlist_rank);
        $this->assertDatabaseHas('application_status_logs', [
            'application_id' => $application->id, 'to_status' => 'waitlisted', 'waitlist_rank' => 2,
        ]);
        $this->assertDatabaseHas('application_status_logs', [
            'application_id' => $application->id, 'from_status' => 'waitlisted',
            'to_status' => 'accepted', 'changed_by' => $staff->id,
        ]);
    }
}
