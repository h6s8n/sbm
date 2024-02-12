<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('event_reserve_id')->unsigned();
            $table->integer('rate')->nullable('false')->default(3);
            $table->string('comment', 250)->nullable(true);
            $table->tinyInteger('type')->default(1);
            $table->timestamps();

            $table->foreign('event_reserve_id')
            ->references('id')
            ->on('event_reserves');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rates');
    }
}
