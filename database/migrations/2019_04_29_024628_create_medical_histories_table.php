<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMedicalHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('job')->nullable();
            $table->string('bloodtype')->nullable();

            $table->integer('severeـillnessـchek')->default(0);
            $table->string('severeـillness')->nullable();

            $table->integer('smoke_chek')->default(0);
            $table->string('smoke')->nullable();

            $table->integer('drink_alcohol_chek')->default(0);
            $table->string('drink_alcohol')->nullable();

            $table->integer('addiction')->default(0);
            $table->string('addiction_text')->nullable();

            $table->integer('medications_chek')->default(0);
            $table->string('medications')->nullable();

            $table->integer('regular_exercise')->default(0);
            $table->string('regular_exercise_text')->nullable();

            $table->integer('alergies')->default(0);
            $table->string('alergies_text')->nullable();

            $table->integer('hereditary_illness')->default(0);
            $table->string('hereditary_illness_text')->nullable();

            $table->integer('history_disease_1')->default(0);
            $table->integer('history_disease_2')->default(0);
            $table->integer('history_disease_3')->default(0);
            $table->integer('history_disease_4')->default(0);
            $table->integer('history_disease_5')->default(0);
            $table->integer('history_disease_6')->default(0);
            $table->integer('history_disease_7')->default(0);
            $table->integer('history_disease_8')->default(0);
            $table->integer('history_disease_9')->default(0);
            $table->integer('history_disease_10')->default(0);
            $table->integer('history_disease_11')->default(0);
            $table->integer('history_disease_12')->default(0);
            $table->integer('history_disease_13')->default(0);

            $table->text('medicalnote')->nullable();
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
        Schema::dropIfExists('medical_histories');
    }
}
