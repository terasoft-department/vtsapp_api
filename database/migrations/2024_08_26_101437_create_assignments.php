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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id('assignment_id'); // Primary key
            $table->integer('customer_id')->nullable(); // Foreign key
            $table->string('plate_number'); // Plate number
            $table->string('customer_phone'); // Customer phone number
            $table->string('location'); // Location
            $table->integer('user_id')->nullable(); // Foreign key
            $table->integer('report_id')->nullable(); // Report ID
             $table->integer('imei_number')->nullable();
              $table->integer('assigned_by')->nullable(); // Report ID
             $table->string('status')->nullable();
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
