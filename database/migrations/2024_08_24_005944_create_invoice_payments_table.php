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
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id('invoice_id'); // Primary key
            $table->string('invoice_number'); // Invoice number
            $table->date('invoice_date'); // Invoice date
            $table->integer('customer_id')->nullable(); // Foreign key
            $table->string('status'); // Status of the invoice
            $table->string('invoice_pdf_file')->nullable(); // Path to the invoice PDF file, nullable
            $table->string('plate_number'); // Plate number associated with the invoice
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};

