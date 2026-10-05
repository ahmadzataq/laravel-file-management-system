<?php

namespace Database\Seeders;

use App\Models\Folder;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * A small tree with three levels:
     *
     *   Company Policies/{HR Policies, IT Policies}
     *   Finance/2026/{Invoices, Reports}
     *   Projects/Project Alpha
     */
    public function run(): void
    {
        $policies = Folder::create(['name' => 'Company Policies']);
        Folder::create(['name' => 'HR Policies', 'parent_id' => $policies->id]);
        Folder::create(['name' => 'IT Policies', 'parent_id' => $policies->id]);

        $finance = Folder::create(['name' => 'Finance']);
        $year = Folder::create(['name' => '2026', 'parent_id' => $finance->id]);
        Folder::create(['name' => 'Invoices', 'parent_id' => $year->id]);
        Folder::create(['name' => 'Reports', 'parent_id' => $year->id]);

        $projects = Folder::create(['name' => 'Projects']);
        Folder::create(['name' => 'Project Alpha', 'parent_id' => $projects->id]);
    }
}
