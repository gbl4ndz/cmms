<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Engine Hours", "Odometer"
            $table->string('unit'); // km, hours, cycles, etc.
            $table->integer('frequency'); // maintenance interval in units
            $table->decimal('current_reading', 12, 2)->default(0);
            $table->decimal('last_maintenance_reading', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('asset_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meters');
    }
};
