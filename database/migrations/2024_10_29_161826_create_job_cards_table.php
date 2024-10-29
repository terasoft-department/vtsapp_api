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
            $table->integer('customer_id')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('vehicle_regNo')->nullable();
            $table->string('title')->nullable();
            $table->string('physical_location')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('problem_reported')->nullable();
            $table->string('natureOf_ProblemAt_site')->nullable();
            $table->string('service_type')->nullable();
            $table->date('date_attended')->nullable();
            $table->string('work_done')->nullable();
            $table->string('imei_number')->nullable();
            $table->string('client_comment')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('pre_workdone_picture')->nullable(); // Pre-work image URL (nullable)
            $table->string('post_workdone_picture')->nullable(); // Post-work image URL (nullable)
            $table->string('carPlateNumber_picture')->nullable(); // Car plate image URL (nullable)
            $table->string('tampering_evidence_picture')->nullable(); // Evidence image URL (nullable)
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
