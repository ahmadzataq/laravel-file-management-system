<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();

            // Adjacency list: a root folder has parent_id = NULL, there is no depth limit.
            // Deleting a folder deletes its whole subtree (ON DELETE CASCADE).
            $table->foreignId('parent_id')->nullable()->constrained('folders')->cascadeOnDelete();

            $table->string('name');
            $table->timestamps();

            // Sibling folders must have different names. PostgreSQL treats NULLs as distinct,
            // so uniqueness of ROOT folder names is additionally checked in the FormRequest.
            $table->unique(['parent_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('folders');
    }
};
