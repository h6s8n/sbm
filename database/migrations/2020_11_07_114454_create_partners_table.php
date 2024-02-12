<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',100);
            $table->string('logo',250);
            $table->string('phone',100);
            $table->string('biography',300);
            $table->string('address',300);
            $table->string('location',250);
            $table->string('times',250);
            $table->string('token',8);
            $table->integer('doctor_percent')->default(0);
            $table->integer('partner_percent')->default(0);
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
        Schema::dropIfExists('partners');
    }
}
