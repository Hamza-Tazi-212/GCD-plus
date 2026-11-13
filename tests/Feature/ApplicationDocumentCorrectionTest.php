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

class ApplicationDocumentCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_candidate_can_replace_the_requested_piece_before_the_commission_decides(): void
    {
        Mail::fake();
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        $subject = ResearchSubject::firstOrFail();
        $candidate = User::factory()->create(['is_active' => true]);
        $staff = User::factory()->create(['role' => UserRole::Staff, 'is_active' => true]);
        $application = app(ApplicationService::class)->submit($candidate, $subject, null, UploadedFile::fake()->create('lettre.pdf'));
        $document = $application->documents()->sole();
        $oldPath = $document->disk_path;
        $ask = route('application-documents.correction', $document);
        $replace = route('application-documents.replace', $document);
        $decision = route('staff.application.review', $application);
        $reason = ['reason' => 'Le document est illisible.'];

        $this->actingAs($staff)->post($ask, $reason)->assertForbidden();
        $staff->staffAssignments()->create(['research_subject_id' => $subject->id, 'function' => StaffFunction::Selection]);
        $this->get(route('staff.application', $application))->assertOk()->assertSee('Demander un remplacement');
        $this->post($ask, $reason)->assertRedirect();
        $this->post($ask, $reason)->assertConflict();
        $this->patch($decision, ['status' => 'rejected', 'comment' => 'Décision.'])->assertSessionHasErrors('status');
        $correction = $document->corrections()->sole();
        $replacement = ['correction' => $correction->id, 'document' => UploadedFile::fake()->create('corrige.pdf', 10, 'application/pdf')];
        $this->patch($replace, $replacement)->assertForbidden();
        $this->actingAs($candidate)->get(route('candidate.applications.show', $application))->assertOk()
            ->assertSee('Le document est illisible.')->assertSee('Scanner avec la caméra')
            ->assertSee('Lettre de motivation à remplacer')->assertSee('href="#replacement-'.$document->id.'"', false);
        $this->assertSame(1, $candidate->unreadNotifications()->count());
        $this->patch($replace, $replacement)->assertRedirect();
        $this->patch($replace, $replacement)->assertConflict();
        $document->refresh();
        $this->assertSame('corrige.pdf', $document->original_name);
        $this->assertNotNull($correction->fresh()->resolved_at);
        $this->get(route('candidate.applications.show', $application))->assertOk()->assertDontSee('Lettre de motivation à remplacer');
        Storage::disk('local')->assertExists($document->disk_path);
        Storage::disk('local')->assertMissing($oldPath);

        $this->actingAs($staff)->patch($decision, ['status' => 'rejected', 'comment' => 'Décision.'])->assertRedirect();
        $this->post($ask, $reason)->assertConflict();
    }
}
