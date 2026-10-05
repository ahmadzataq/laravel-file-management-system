<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SearchAndFilterTest extends TestCase
{
    use RefreshDatabase;

    private Folder $folderA;

    private Department $finance;

    private Department $hr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->folderA = Folder::factory()->create();
        $folderB = Folder::factory()->create();
        $this->finance = Department::factory()->create(['name' => 'Finance']);
        $this->hr = Department::factory()->create(['name' => 'Human Resources']);

        Document::factory()->create([
            'title' => 'Annual Budget',
            'original_name' => 'budget-2026.xlsx',
            'folder_id' => $this->folderA->id,
            'department_id' => $this->finance->id,
        ]);
        Document::factory()->create([
            'title' => 'Employee Handbook',
            'original_name' => 'handbook.pdf',
            'folder_id' => $folderB->id,
            'department_id' => $this->hr->id,
        ]);

        $this->actingAs(User::factory()->viewer()->create());
    }

    /** @return list<string> */
    private function titlesFor(array $query, ?Folder $folder = null): array
    {
        $url = $folder ? route('folders.show', $folder) : route('folders.index');

        $titles = [];

        $this->get($url.'?'.http_build_query($query))
            ->assertOk()
            ->assertInertia(function (Assert $page) use (&$titles) {
                $titles = collect($page->toArray()['props']['documents']['data'])->pluck('title')->all();
            });

        return $titles;
    }

    public function test_search_by_title_is_case_insensitive(): void
    {
        $this->assertSame(['Annual Budget'], $this->titlesFor(['q' => 'BUDGET']));
    }

    public function test_search_by_file_name(): void
    {
        $this->assertSame(['Employee Handbook'], $this->titlesFor(['q' => 'handbook.pdf']));
    }

    public function test_search_by_department_name(): void
    {
        $this->assertSame(['Annual Budget'], $this->titlesFor(['q' => 'finance']));
    }

    public function test_filter_by_department(): void
    {
        $this->assertSame(['Employee Handbook'], $this->titlesFor(['department' => $this->hr->id]));
    }

    public function test_search_and_department_filter_can_be_combined(): void
    {
        $this->assertSame([], $this->titlesFor(['q' => 'budget', 'department' => $this->hr->id]));
        $this->assertSame(['Annual Budget'], $this->titlesFor(['q' => 'budget', 'department' => $this->finance->id]));
    }

    public function test_without_filters_only_the_current_folder_is_listed(): void
    {
        $this->assertSame(['Annual Budget'], $this->titlesFor([], $this->folderA));
    }

    public function test_search_covers_all_folders_even_when_inside_a_folder(): void
    {
        $this->assertSame(['Employee Handbook'], $this->titlesFor(['q' => 'handbook'], $this->folderA));
    }

    public function test_results_are_paginated(): void
    {
        $folder = Folder::factory()->create();
        Document::factory()->count(11)->create(['folder_id' => $folder->id]);

        $this->get(route('folders.show', $folder))
            ->assertInertia(fn (Assert $page) => $page
                ->has('documents.data', 10)
                ->where('documents.meta.total', 11)
                ->where('documents.meta.last_page', 2)
            );
    }
}
