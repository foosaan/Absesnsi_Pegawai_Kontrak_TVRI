<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('leave_balances');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('initial_balance')->default(12);
            $table->integer('used')->default(0);
            $table->integer('remaining')->default(12);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'year']);
        });
    }
};
