<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.2
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('vehicle_id'); // Primary key
            $table->string('vehicle_name')->nullable();
            $table->string('category')->nullable();
            $table->integer('customer_id')->nullable(); // Foreign key
            $table->string('plate_number')->nullable(); // Vehicle plate number
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
