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
        Schema::create('customer_debts', function (Blueprint $table) {
            $table->id('customer_id'); // Primary key
            $table->decimal('debt_burden', 10, 2); // Debt amount
            $table->date('debt_from'); // Start date of the debt
            $table->date('debt_end'); // End date of the debt
            $table->integer('pay_id')->nullable()->nullable(); // Foreign key
            $table->timestamps(); // Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_debts');
    }
};
