<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('token_room');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('doctor_id');
            $table->bigInteger('calender_id');
            $table->string('fa_data');
            $table->timestamp('data');
            $table->integer('time');
            $table->timestamp('last_activity_doctor')->nullable();
            $table->timestamp('last_activity_user')->nullable();
            $table->string('visit_status')->default('not_end');
            $table->string('doctor_payment_status')->default('pending');
            $table->string('status')->default('active');
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
        Schema::dropIfExists('event_reserves');
    }
}
