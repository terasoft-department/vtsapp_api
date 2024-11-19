<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_cards', function (Blueprint $table) {
            $table->id('jobcard_id'); // Primary key
           $table->string('Clientname');
            $table->string('Tel')->nullable();
            $table->string('ContactPerson')->nullable();
            $table->string('title')->nullable();
            $table->string('mobilePhone')->nullable();
            $table->string('VehicleRegNo')->nullable();
            $table->string('physicalLocation')->nullable();
            $table->string('deviceID')->nullable();
            $table->text('problemReported')->nullable();
            $table->date('DateReported')->nullable();
            $table->date('DateAttended')->nullable();
            $table->string('natureOfProblem')->nullable();
            $table->string('workDone')->nullable();
            $table->string('clientComment')->nullable();
            $table->string('service_type');
            $table->foreignId('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_cards');
    }
}
