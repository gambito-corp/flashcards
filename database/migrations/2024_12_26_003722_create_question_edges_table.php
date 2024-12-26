<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionEdgesTable extends Migration
{
    public function up()
    {
        Schema::create('question_edges', function (Blueprint $table) {
            $table->id();

            // Pregunta de origen
            $table->foreignId('from_question_id')
                ->constrained('questions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Opción que produce la transición (puede ser null)
            $table->foreignId('option_id')
                ->nullable()
                ->constrained('options')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Pregunta de destino
            $table->foreignId('to_question_id')
                ->constrained('questions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

             $table->boolean('is_default')->default(false); // si lo necesitas
             $table->text('condition')->nullable();        // condicional extra

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_edges');
    }
}
