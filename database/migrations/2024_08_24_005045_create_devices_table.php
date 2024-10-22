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
        Schema::create('devices', function (Blueprint $table) {
            $table->id('device_id'); // Primary key
            $table->string('device_name'); // Device name
            $table->string('imei_number'); // IMEI number
            $table->string('device_model'); // Device model
            $table->enum('category', ['master', 'slave', 'accessories']); // Category
            $table->integer('total'); // Total quantity
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
