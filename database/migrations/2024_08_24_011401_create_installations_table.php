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
        Schema::create('installations', function (Blueprint $table) {
            $table->id('installation_id'); // Primary key
             $table->string('plate_number')->nullable();
            $table->string('imei_number')->nullable();
             $table->string('customername')->nullable();
                $table->integer('user_id')->nullable();
            $table->decimal('amount_paid', 10, 2); // Amount paid
            $table->string('status')->nullable(); // Status of the installation
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installations');
    }
};




