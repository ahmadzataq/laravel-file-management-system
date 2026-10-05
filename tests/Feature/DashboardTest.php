<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_dashboard_shows_totals_and_the_ten_latest_files(): void
    {
        $folder = Folder::factory()->create();
        $department = Department::factory()->create();

        Document::factory()->count(12)->create([
            'folder_id' => $folder->id,
            'department_id' => $department->id,
        ]);

        $this->actingAs(User::factory()->viewer()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('stats.folders', 1)
                ->where('stats.documents', 12)
                ->where('stats.departments', 1)
                ->has('latestDocuments', 10)
                ->where('latestDocuments.0.id', Document::max('id'))
            );
    }

    public function test_the_role_is_shared_with_the_front_end(): void
    {
        $this->actingAs(User::factory()->administrator()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('auth.isAdmin', true));

        $this->actingAs(User::factory()->viewer()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('auth.isAdmin', false));
    }
}
