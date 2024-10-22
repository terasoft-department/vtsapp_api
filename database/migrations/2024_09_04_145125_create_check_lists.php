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
        Schema::create('check_lists', function (Blueprint $table) {
            $table->id('check_id'); // Primary key
            $table->unsignedBigInteger('user_id'); // Foreign key to users table
            $table->unsignedBigInteger('vehicle_id')->nullable(); // Foreign key to vehicles table
            $table->unsignedBigInteger('customer_id')->nullable(); // Foreign key to customers table
            $table->string('plate_number')->nullable(); // Vehicle plate number
            $table->string('rbt_status')->nullable(); // RBT status
            $table->string('batt_status')->nullable(); // BATT status
            $table->date('check_date')->nullable(); // Date of the check
            $table->timestamps(); // Adds created_at and updated_at columns

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_lists');
    }
};
