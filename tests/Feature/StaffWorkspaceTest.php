<?php

namespace Tests\Feature;

use App\Enums\StaffFunction;
use App\Enums\UserRole;
use App\Models\ResearchSubject;
use App\Models\User;
use Database\Seeders\EstfDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_login_and_scoped_dashboards_for_every_function(): void
    {
        $this->seed(EstfDemoSeeder::class);
        [$subject, $other] = ResearchSubject::limit(2)->get()->all();
        $staff = User::factory()->create(['role' => UserRole::Staff, 'is_active' => true]);
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $this->actingAs($admin)->get(route('admin.staff.index'))->assertOk();
        foreach (StaffFunction::cases() as $function) {
            $this->post(route('admin.staff.store'), [
                'user_id' => $staff->id, 'research_subject_id' => $subject->id, 'function' => $function->value,
            ])->assertRedirect()->assertSessionHasNoErrors();
        }
        $this->post(route('logout'));
        $this->post(route('login'), ['email' => $staff->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->get(route('dashboard'))->assertRedirect(route('staff.workspace'));
        foreach (StaffFunction::cases() as $function) {
            $this->get(route('staff.workspace', ['fonction' => $function->value]))
                ->assertOk()->assertSee($function->label())->assertSee($subject->title)->assertDontSee($other->title);
        }
        $this->get(route('admin.staff.index'))->assertForbidden();
        $staff->staffAssignments()->where('function', StaffFunction::Lab->value)->delete();
        $this->get(route('staff.workspace', ['fonction' => StaffFunction::Lab->value]))->assertForbidden();
    }
}
