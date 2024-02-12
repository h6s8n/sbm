<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestCodesLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_codes_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mobile',50);
            $table->string('code',8);
            $table->string('ip',20);
            $table->string('browser',50)->nullable(true);
            $table->string('os',50)->nullable(true);
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
        Schema::dropIfExists('request_codes_logs');
    }
}
