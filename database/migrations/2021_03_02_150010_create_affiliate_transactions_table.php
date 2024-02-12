<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliate_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('affiliate_id');
            $table->unsignedBigInteger('event_id');
            $table->string('total');
            $table->string('amount');
            $table->unsignedTinyInteger('status')->default(0);

            $table->foreign('user_id')
                ->on('users')
                ->references('id');

            $table->foreign('doctor_id')
                ->on('users')
                ->references('id');

            $table->foreign('affiliate_id')
                ->on('users')
                ->references('id');

            $table->foreign('event_id')
                ->on('event_reserves')
                ->references('id');

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
        Schema::dropIfExists('affiliate_transactions');
    }
}
