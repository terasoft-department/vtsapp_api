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
        Schema::create('new_installations', function (Blueprint $table) {
             $table->id();
            $table->string('customerName');
            $table->string('plateNumber')->nullable();
            $table->string('DeviceNumber')->nullable();
            $table->string('CarRegNumber')->nullable();
            $table->string('customerPhone')->nullable();
            $table->string('simCardNumber')->nullable();
            $table->string('picha_ya_gari_kwa_mbele')->nullable();
            $table->string('picha_ya_device_anayoifunga')->nullable();
            $table->string('picha_ya_hiyo_karatasi_ya_simCardNumber')->nullable();
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_installations');
    }
};
