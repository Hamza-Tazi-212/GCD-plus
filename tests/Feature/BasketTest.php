<?php

namespace Tests\Feature;

use App\Models\ResearchSubject;
use App\Models\User;
use App\Services\ApplicationService;
use Database\Seeders\EstfDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BasketTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_keep_and_remove_a_subject_after_login(): void
    {
        $this->seed(EstfDemoSeeder::class);
        $subject = ResearchSubject::firstOrFail();
        $user = User::factory()->create();

        $this->from(route('home'))->post(route('basket.add', $subject))->assertRedirect(route('home'));
        $this->assertGuest();
        $this->get(route('basket'))->assertSee($subject->title);
        $this->get(route('candidate.applications.create', $subject))->assertRedirect(route('login'));
        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHas('basket', [$subject->id]);
        $this->delete(route('basket.remove', $subject))->assertRedirect(route('basket'));
        $this->get(route('basket'))->assertDontSee($subject->title);
    }

    public function test_dashboard_only_shows_the_candidates_upcoming_interview(): void
    {
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        [$subject, $otherSubject, $pastSubject] = ResearchSubject::all()->all();
        $candidate = User::factory()->create(["is_active" => true]);
        $other = User::factory()->create();
        $service = app(ApplicationService::class);

        foreach ([[$candidate, $subject, now()->addDay()], [$other, $otherSubject, now()->addDay()], [$candidate, $pastSubject, now()->subDay()]] as [$user, $item, $date]) {
            $application = $service->submit($user, $item, null, UploadedFile::fake()->create('lettre.pdf'));
            $application->update(['status' => \App\Enums\ApplicationStatus::UnderReview, 'oral_exam_at' => $date]);
        }

        $this->actingAs($candidate)->get(route('candidate.dashboard'))
            ->assertOk()->assertSee('Prochains entretiens')->assertSee('Mon panier')
            ->assertSee($subject->title)->assertDontSee($otherSubject->title)
            ->assertViewHas('interviews', fn ($interviews) => $interviews->pluck('subject_id')->all() === [$subject->id]);
    }

    public function test_professor_can_filter_own_applications_by_campaign_only(): void
    {
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        [$current, $future, $unrelated] = ResearchSubject::all()->all();
        $future->forceFill(['campaign' => '2027–2028'])->save();
        $future->update(['is_open' => true]);
        $unrelated->update(['professor_id' => User::factory()->professor()->create()->id]);
        $unrelated->update(['is_open' => true]);
        $candidate = User::factory()->create();
        $service = app(ApplicationService::class);
        foreach ([$current, $future, $unrelated] as $subject) {
            $applications[$subject->id] = $service->submit($candidate, $subject, null, UploadedFile::fake()->create('lettre.pdf'));
        }

        $this->actingAs($current->professor);
        $this->get(route('professor.dashboard'))->assertOk()->assertSee('Tableau de bord encadrant');
        $this->get(route('professor.applications.index', ['campaign' => $future->campaign]))
            ->assertOk()->assertViewHas('applications', fn ($page) => $page->getCollection()->pluck('subject_id')->all() === [$future->id]);
        $this->get(route('professor.applications.show', $applications[$unrelated->id]))->assertForbidden();
    }

    public function test_fourth_submission_in_same_campaign_is_blocked(): void
    {
        Storage::fake('local');
        $this->seed(EstfDemoSeeder::class);
        $user = User::factory()->create();
        $service = app(ApplicationService::class);
        foreach (ResearchSubject::orderBy('id')->limit(3)->get() as $subject) {
            $service->submit($user, $subject, null, UploadedFile::fake()->create('lettre.pdf'));
        }
        $fourth = $subject->replicate();
        $fourth->title = 'Quatrième sujet';
        $fourth->save();

        try {
            $service->submit($user, $fourth, null, UploadedFile::fake()->create('lettre.pdf'));
            $this->fail('The campaign quota was not enforced.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('application', $exception->errors());
        }
        $this->assertDatabaseCount('applications', 3);

        $fourth = $fourth->replicate();
        $fourth->forceFill(['campaign' => '2027–2028', 'title' => 'Sujet de la prochaine campagne'])->save();
        $service->submit($user, $fourth, null, UploadedFile::fake()->create('lettre.pdf'));
        $this->assertDatabaseCount('applications', 4);
    }
}
