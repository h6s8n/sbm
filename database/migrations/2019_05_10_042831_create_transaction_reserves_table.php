<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionReservesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_reserves', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('doctor_id');
            $table->bigInteger('calender_id');
            $table->string('token');
            $table->bigInteger('amount')->default(0);
            $table->bigInteger('used_credit')->default(0);
            $table->bigInteger('amount_paid')->default(0);
            $table->string('transId')->nullable();
            $table->string('factorNumber')->nullable();
            $table->string('message')->nullable();
            $table->string('status')->default('pending');
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
        Schema::dropIfExists('transaction_reserves');
    }
}
