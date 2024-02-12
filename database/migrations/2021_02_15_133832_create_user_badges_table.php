<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserBadgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_badges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('badge_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('last_changed_user_id');
            $table->dateTime('activation_time');
            $table->dateTime('expiration_time');
            $table->timestamps();

            $table->foreign('badge_id')
                ->on('badges')
                ->references('id')
                ->onDelete('CASCADE');

            $table->foreign('user_id')
                ->on('users')
                ->references('id')
                ->onDelete('CASCADE');

            $table->foreign('last_changed_user_id')
                ->on('users')
                ->references('id')
                ->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_badges');
    }
}
