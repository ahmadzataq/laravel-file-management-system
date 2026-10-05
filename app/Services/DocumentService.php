<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DocumentService
{
    /**
     * Store the uploaded file on disk and create its database record.
     *
     * @param  array{folder_id: int, department_id: int, title: string}  $data
     */
    public function store(array $data, UploadedFile $file, User $uploader): Document
    {
        $path = $file->store('documents', Document::DISK);

        try {
            return Document::create([
                'folder_id' => $data['folder_id'],
                'department_id' => $data['department_id'],
                'uploaded_by' => $uploader->id,
                'title' => $data['title'],
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        } catch (Throwable $e) {
            // Do not leave an orphan file behind when the insert fails.
            Storage::disk(Document::DISK)->delete($path);

            throw $e;
        }
    }

    public function delete(Document $document): void
    {
        $document->delete();

        Storage::disk(Document::DISK)->delete($document->path);
    }
}
