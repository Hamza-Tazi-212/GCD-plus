<?php

namespace Tests\Feature;

use App\Models\CampaignPeriod;
use App\Models\ResearchSubject;
use App\Models\User;
use App\Services\ApplicationService;
use Database\Seeders\EstfDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CampaignCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sets_public_dates_and_application_window_is_enforced(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-13 12:00', 'Africa/Casablanca'));
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        $subject = ResearchSubject::firstOrFail();
        $candidate = User::factory()->create(['is_active' => true]);
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $data = ['academic_year' => '2026-2027', 'phase' => 'application', 'opens_on' => '2026-11-14', 'closes_on' => '2026-11-15'];
        $this->actingAs($candidate)->post(route('admin.calendar.store'), $data)->assertForbidden();
        $this->actingAs($admin)->get(route('admin.calendar.edit'))->assertOk();
        $this->post(route('admin.calendar.store'), array_replace($data, ['closes_on' => '2026-11-12']))
            ->assertSessionHasErrors('closes_on');
        $this->post(route('admin.calendar.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->get(route('calendar'))->assertOk()->assertSee('À venir')->assertSee('14/11/2026');
        $this->assertDatabaseHas('campaign_periods', ['academic_year' => '2026–2027']);

        $this->post(route('logout'));
        $this->get(route('home'))->assertOk()->assertSee('Candidature · À venir')->assertSee('15/11/2026');
        $this->get(route('public.subjects.show', $subject))->assertOk()
            ->assertSee('Consulter le calendrier')->assertDontSee('Se connecter pour postuler');
        $this->from(route('home'))->post(route('basket.add', $subject))->assertRedirect();
        $this->get(route('basket'))->assertOk()->assertSee('Candidature · À venir')->assertDontSee('>Postuler</a>', false);

        try {
            app(ApplicationService::class)->submit($candidate, $subject, null, UploadedFile::fake()->create('lettre.pdf'));
            $this->fail('Submission was accepted before opening.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('application', $exception->errors());
        }
        $this->assertDatabaseCount('applications', 0);

        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-15 23:59', 'Africa/Casablanca'));
        $this->assertTrue(CampaignPeriod::firstOrFail()->isOpen());
        $this->get(route('basket'))->assertOk()->assertSee('Candidature · Ouverte')->assertSee('>Postuler</a>', false);
        app(ApplicationService::class)->submit($candidate, $subject, null, UploadedFile::fake()->create('lettre.pdf'));
        $this->assertDatabaseCount('applications', 1);
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-16 00:00', 'Africa/Casablanca'));
        $this->assertFalse(CampaignPeriod::firstOrFail()->isOpen());
        $this->get(route('calendar'))->assertOk()->assertSee('Clôturée');
    }

    public function test_campaign_checklist_changes_candidate_requirements_and_confirmation(): void
    {
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        $subject = ResearchSubject::firstOrFail();
        $candidate = User::factory()->create(['is_active' => true]);
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $application = app(ApplicationService::class)->submit($candidate, $subject, null, UploadedFile::fake()->create('lettre.pdf'));
        $application->update(['status' => 'accepted']);
        $configure = route('admin.calendar.checklist');
        $data = ['campaign' => '2026-2027', 'kinds' => ['registration_form']];

        $this->actingAs($candidate)->post($configure, $data)->assertForbidden();
        $this->actingAs($admin)->post($configure, array_replace($data, ['kinds' => ['invalid']]))->assertSessionHasErrors('kinds.0');
        $this->post($configure, $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('enrolments.confirm', $application), ['verified' => 1])->assertSessionHasErrors('verified');
        $this->actingAs($candidate)->get(route('enrolment-documents.show', $application))
            ->assertOk()->assertSee('Fiche d’inscription')->assertDontSee('Pièce d’identité');
        $pdf = UploadedFile::fake()->create('fiche.pdf', 10, 'application/pdf');
        $this->post(route('enrolment-documents.upload', [$application, 'identity']), ['document' => $pdf])->assertNotFound();
        $this->post(route('enrolment-documents.upload', [$application, 'registration_form']), ['document' => $pdf])->assertRedirect();
        $document = $application->enrolmentDocuments()->sole();
        $this->actingAs($admin)->patch(route('enrolment-documents.review', [$application, $document]), [
            'status' => 'approved', 'version' => hash('sha256', $document->path),
        ])->assertRedirect();
        $this->post(route('enrolments.confirm', $application), ['verified' => 1])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('doctoral_enrolments', ['application_id' => $application->id]);
    }
}
