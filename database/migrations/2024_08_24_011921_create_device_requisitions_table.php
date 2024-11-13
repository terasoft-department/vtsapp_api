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
        Schema::create('device_requisitions', function (Blueprint $table) {
            $table->id('requisition_id'); // Primary key
            $table->integer('user_id')->nullable(); // Foreign key
            $table->text('descriptions')->nullable(); // Additional descriptions
            $table->string('status')->nullable(); // Status of the requisition
            $table->date('dateofProvision')->nullable(); // Date of provision
            $table->integer('master')->default(0); // Master attribute as integer
            $table->integer('I_button')->default(0); // I_button attribute as integer
            $table->integer('buzzer')->default(0); // Buzzer attribute as integer
             $table->text('dispatched_imeis')->default('available');
             $table->text('dispatched_status')->default('available');
            $table->integer('panick_button')->default(0); // Panick_button attribute as integer
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_requisitions');
    }
};

