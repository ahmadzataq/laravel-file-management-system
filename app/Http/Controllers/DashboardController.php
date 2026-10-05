<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResource;
use App\Models\Department;
use App\Models\Document;
use App\Models\Folder;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $latestDocuments = Document::query()
            ->with(['folder:id,name', 'department:id,name', 'uploader:id,name'])
            ->latest()
            ->latest('id')
            ->limit(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'folders' => Folder::count(),
                'documents' => Document::count(),
                'departments' => Department::count(),
            ],
            'latestDocuments' => DocumentResource::collection($latestDocuments),
        ]);
    }
}
