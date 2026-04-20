<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('area_code', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['location_id', 'name']);
            $table->index('area_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('areas');
    }
};
