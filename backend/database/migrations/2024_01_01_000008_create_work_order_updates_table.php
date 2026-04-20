<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            // Snapshot the status change so history is preserved
            $table->enum('status_from', ['open', 'in_progress', 'on_hold', 'closed'])->nullable();
            $table->enum('status_to', ['open', 'in_progress', 'on_hold', 'closed'])->nullable();
            $table->enum('type', ['comment', 'status_change', 'assignment'])->default('comment');
            $table->timestamps();

            $table->index('work_order_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_updates');
    }
};
