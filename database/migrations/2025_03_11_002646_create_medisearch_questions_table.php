<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMedisearchQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('medisearch_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('query'); // La pregunta del usuario
            $table->json('response')->nullable(); // La respuesta completa (en formato JSON)
            $table->timestamps();

            // Relación con la tabla de usuarios
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('medisearch_questions');
    }
}
