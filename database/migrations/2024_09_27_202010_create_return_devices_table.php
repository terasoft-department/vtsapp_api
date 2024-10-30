<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_devices', function (Blueprint $table) {
            $table->id('return_id'); // Primary key
            $table->foreignId('user_id')->nullable(); // Foreign key to users table
            $table->string('plate_number', 255); // Vehicle plate number
             $table->foreignId('imei_numner')->nullable(); // Foreign key to vehicles table
            $table->foreignId('customer_id')->nullable(); // Foreign key to customers table
            $table->text('reason'); // Reason for return
            $table->string('status')->nullable(); // Status of the return
            $table->timestamps(); // Created at and Updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_devices');
    }
}
