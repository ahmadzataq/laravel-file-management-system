<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('folder_id')->constrained()->cascadeOnDelete();

            // A department that still has files cannot be deleted.
            $table->foreignId('department_id')->constrained()->restrictOnDelete();

            // Keep the file when the uploader account is removed.
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('original_name');   // name shown to the user and used when downloading
            $table->string('path');            // location on the storage disk (hashed name)
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);   // bytes
            $table->timestamps();

            // PostgreSQL does not create indexes for foreign keys automatically.
            $table->index('folder_id');
            $table->index('department_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
