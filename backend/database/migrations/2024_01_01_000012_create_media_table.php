<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Polymorphic media table — can attach files to assets, work orders, etc.
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable'); // mediable_id + mediable_type + index
            $table->string('disk')->default('public');
            $table->string('path'); // storage path
            $table->string('filename'); // original filename
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // bytes
            $table->enum('collection', ['images', 'documents', 'manuals', 'other'])->default('other');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id', 'collection']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
