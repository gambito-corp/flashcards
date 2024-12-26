<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // enunciado de la pregunta

            // Distintos tipos de pregunta
            $table->enum('question_type', ['multiple_choice','boolean','range'])
                ->default('multiple_choice');

            // Indica si es parte de un DAG
            $table->boolean('is_dag')->default(false);

            // Campos de rango
            $table->integer('range_min')->nullable();
            $table->integer('range_max')->nullable();

            // Multimedia opcional
            $table->string('media_type', 50)->nullable();
            $table->text('media_url')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
