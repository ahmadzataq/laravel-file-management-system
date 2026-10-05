<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_create_update_and_delete_a_department(): void
    {
        $this->actingAs(User::factory()->administrator()->create());

        $this->post(route('departments.store'), ['name' => 'Legal'])->assertRedirect();
        $department = Department::where('name', 'Legal')->firstOrFail();

        $this->put(route('departments.update', $department), ['name' => 'Legal & Compliance'])->assertRedirect();
        $this->assertSame('Legal & Compliance', $department->fresh()->name);

        $this->delete(route('departments.destroy', $department))->assertRedirect();
        $this->assertModelMissing($department);
    }

    public function test_department_names_must_be_unique(): void
    {
        Department::factory()->create(['name' => 'Finance']);

        $this->actingAs(User::factory()->administrator()->create())
            ->post(route('departments.store'), ['name' => 'Finance'])
            ->assertSessionHasErrors('name');
    }

    public function test_a_department_that_is_in_use_cannot_be_deleted(): void
    {
        $document = Document::factory()->create();

        $this->actingAs(User::factory()->administrator()->create())
            ->delete(route('departments.destroy', $document->department))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertModelExists($document->department);
    }

    public function test_viewers_cannot_access_department_management(): void
    {
        $department = Department::factory()->create();

        $this->actingAs(User::factory()->viewer()->create());

        $this->get(route('departments.index'))->assertForbidden();
        $this->post(route('departments.store'), ['name' => 'Nope'])->assertForbidden();
        $this->put(route('departments.update', $department), ['name' => 'Nope'])->assertForbidden();
        $this->delete(route('departments.destroy', $department))->assertForbidden();
    }
}
