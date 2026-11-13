<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('École Supérieure de Technologie de Fès');
        $this->seed(\Database\Seeders\EstfDemoSeeder::class);
        $subject = \App\Models\ResearchSubject::firstOrFail();
        $this->get(route('public.subjects.show', $subject))->assertOk()->assertSee($subject->title);
        $subject->update(['is_open' => false]);
        $this->get(route('public.subjects.show', $subject))->assertNotFound();
    }

    public function test_public_and_candidate_catalogues_show_nine_subjects_per_page(): void
    {
        $this->seed(\Database\Seeders\EstfDemoSeeder::class);

        $first = $this->get(route('home'))->assertOk()->viewData('subjects');
        $second = $this->get(route('home', ['page' => 2]))->assertOk()->viewData('subjects');
        $this->assertSame(21, $first->total());
        $this->assertCount(9, $first->items());
        $this->assertCount(9, $second->items());
        $this->assertSame([], array_intersect($first->pluck('id')->all(), $second->pluck('id')->all()));

        $third = $this->get(route('home', ['page' => 3]))->assertOk()->viewData('subjects');
        $this->assertCount(3, $third->items());
        $this->assertSame([], array_intersect($third->pluck('id')->all(), $first->pluck('id')->merge($second->pluck('id'))->all()));

        $candidate = \App\Models\User::factory()->create(['is_active' => true]);
        $this->actingAs($candidate)->get(route('candidate.subjects.index', ['page' => 2]))
            ->assertOk()->assertViewHas('subjects', fn ($subjects) => $subjects->count() === 9 && $subjects->lastPage() === 3);
    }
    public function test_catalogues_combine_laboratory_and_field_and_preserve_filters_in_pagination(): void
    {
        $this->seed(\Database\Seeders\EstfDemoSeeder::class);
        $lab = 'Technologies et Services Industriels (TSI)';
        $filters = ['laboratory' => $lab, 'field' => 'Logistique'];
        $this->get(route('home', $filters))->assertOk()->assertSee('Filtres actifs')->assertSee('Réinitialiser')
            ->assertViewHas('subjects', fn ($subjects) => $subjects->total() === 3
                && $subjects->every(fn ($subject) => $subject->laboratory === $lab && $subject->field === 'Logistique'));
        $page = $this->get(route('home', ['laboratory' => $lab]))->assertOk()->viewData('subjects');
        $this->assertCount(9, $page->items());
        parse_str(parse_url($page->nextPageUrl(), PHP_URL_QUERY), $query);
        $this->assertSame($lab, $query['laboratory']);

        $candidate = \App\Models\User::factory()->create(['is_active' => true]);
        $this->actingAs($candidate)->get(route('candidate.subjects.index', $filters))
            ->assertOk()->assertSee('Domaine · Logistique')
            ->assertViewHas('subjects', fn ($subjects) => $subjects->total() === 3);
    }
}
