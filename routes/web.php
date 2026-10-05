<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Folders (explorer): index = top level, show = inside a folder
    Route::resource('folders', FolderController::class)->except(['create', 'edit']);

    // Documents (files). Declared before the resource so "download"/"preview" never clash with {document}
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/preview', [DocumentController::class, 'preview'])->name('documents.preview');
    Route::resource('documents', DocumentController::class)->except(['index']);

    // Departments (administrator only, enforced by DepartmentPolicy)
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);

    // Profile (from Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
