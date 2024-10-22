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
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id'); // Primary key
            $table->string('customername'); // Customer name
            $table->string('address')->nullable(); // Customer address, nullable field
            $table->string('customer_phone'); // Customer phone number
            $table->string('TinNumber')->nullable(); // TIN Number, nullable field
            $table->string('email')->unique(); // Customer email, must be unique
            $table->date('start_date'); // Customer start date
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

