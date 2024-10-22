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
            $table->foreignId('user_id')->nullable(); // User ID (nullable)
            $table->string('contact_person')->nullable(); // Contact person (nullable)
            $table->string('title')->nullable(); // Title (nullable)
            $table->string('mobile_number')->nullable(); // Mobile number (nullable)
            $table->string('physical_location')->nullable(); // Physical location (nullable)
            $table->string('imei_number')->nullable(); // IMEI number (nullable)
             $table->string('vehicle_regNo')->nullable();
             $table->string('title')->nullable();
            $table->text('problem_reported')->nullable(); // Problem reported (nullable)
            $table->text('natureOf_ProblemAt_site')->nullable(); // Nature of problem at site (nullable)
            $table->string('service_type')->nullable(); // Service type (nullable)
            $table->date('date_attended')->nullable(); // Date attended (nullable)
            $table->text('work_done')->nullable(); // Work done (nullable)
            $table->string('vehicle_regno')->nullable(); // Vehicle registration number (nullable)
            $table->text('client_comment')->nullable(); // Client comment (nullable)
            $table->foreignId('assignment_id')->nullable();
               $table->string('plate_number')->nullable();
            $table->foreignId('customer_id')->nullable(); // Customer ID (nullable)
            $table->string('pre_workdone_picture')->nullable(); // Pre work done picture URL (nullable)
            $table->string('post_workdone_picture')->nullable(); // Post work done picture URL (nullable)
            $table->string('carPlateNumber_picture')->nullable(); // Car plate number picture URL (nullable)
            $table->string('tampering_evidence_picture')->nullable(); // Tampering evidence picture URL (nullable)
            $table->timestamps(); // Created and updated timestamps
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
