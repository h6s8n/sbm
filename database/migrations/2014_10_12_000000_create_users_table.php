<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('nationalcode')->nullable();
            $table->string('username');
            $table->string('name')->nullable();
            $table->string('family')->nullable();
            $table->string('fullname')->nullable();
            $table->integer('gender')->default(1);
            $table->string('birthday')->nullable();
            $table->string('zone')->default("+98");
            $table->integer('show_phone')->default(1);
            $table->integer('country_id')->default(0);
            $table->integer('state_id')->default(0);
            $table->integer('city_id')->default(0);
            $table->string('street')->nullable();
            $table->string('address')->nullable();
            $table->string('picture')->nullable();
            $table->string('phone')->nullable();
            $table->text('bio')->nullable();
            $table->integer('rank')->default(0);
            $table->bigInteger('credit')->default(0);
            $table->text('national_insurance')->nullable();
            $table->string('specialcode')->nullable();
            $table->string('job_title')->nullable();
            $table->string('account_number')->nullable();
            $table->string('approve')->default(2);
            $table->string('doctor_status')->default('inactive');
            $table->string('token')->unique();
            $table->integer('mdical_history_status')->default(0);
            $table->integer('doctor_info_status')->default(0);
            $table->string('doctor_info_alert')->nullable();
            $table->string('doctor_nickname')->default('دکتر');


            $table->integer('doctor_visit_price')->default(20000);
            $table->string('passport_image')->nullable();
            $table->string('national_cart_image')->nullable();
            $table->string('education_image')->nullable();
            $table->string('special_cart_image')->nullable();
            $table->text('special_json')->nullable();
            $table->text('skill_json')->nullable();

            $table->timestamp('last_calender_time')->nullable();

            $table->string('online_status')->default('offline');

            $table->string('status', 80)->default("active");
            $table->string('password', 500);


            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
