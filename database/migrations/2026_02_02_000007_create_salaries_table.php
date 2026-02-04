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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('month'); // 1-12
            $table->integer('year');
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->integer('total_work_days')->default(0);
            $table->integer('total_late_days')->default(0);
            $table->integer('total_absent_days')->default(0);
            $table->decimal('final_salary', 12, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint: one salary record per user per month/year
            $table->unique(['user_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
