<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users');
            $table->decimal('reading_value', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamp('read_at'); // actual reading time, not created_at
            $table->timestamps();

            $table->index('meter_id');
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
