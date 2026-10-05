<?php

namespace App\Http\Resources;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Document */
class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'file_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'previewable' => $this->isPreviewable(),
            'folder_id' => $this->folder_id,
            'department_id' => $this->department_id,
            'folder' => $this->whenLoaded('folder', fn () => [
                'id' => $this->folder->id,
                'name' => $this->folder->name,
            ]),
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'uploaded_by' => $this->whenLoaded('uploader', fn () => $this->uploader?->name),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
