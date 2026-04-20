<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            // Snapshot unit cost at time of use — parts prices change over time
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 10, 2)->storedAs('quantity * unit_cost');
            $table->foreignId('added_by')->constrained('users');
            $table->timestamps();

            $table->index('work_order_id');
            $table->index('part_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_parts');
    }
};
