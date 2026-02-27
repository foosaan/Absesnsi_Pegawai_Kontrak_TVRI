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
        // Drop redundant tables that are now replaced by master_data_types + master_data_values
        Schema::dropIfExists('jabatans');
        Schema::dropIfExists('bagians');
        Schema::dropIfExists('status_pegawais');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate tables if needed
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bagians', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('status_pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
};
