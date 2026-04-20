<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('part_number')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('unit')->default('pcs'); // pcs, kg, liters, etc.
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('minimum_quantity')->default(0); // reorder threshold
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('part_number');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
