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
            $table->foreignId('user_id')->constrained('users');
            $table->text('content');
            $table->enum('question_type', ['multiple_choice', 'boolean', 'range'])->default('multiple_choice');
            $table->integer('range_min')->nullable();
            $table->integer('range_max')->nullable();
            $table->string('media_type', 50)->nullable();
            $table->text('media_url')->nullable();
            $table->text('media_iframe')->nullable();
            $table->boolean('approved')->default(false);
            $table->text('explanation')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabla intermedia para relacionar preguntas con tipos
        Schema::create('question_tipo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('tipo_id')->constrained('tipos')->cascadeOnDelete();
            $table->timestamps();
        });

        // Tabla intermedia para relacionar preguntas con universidades
        Schema::create('question_universidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('universidad_id')->constrained('universidades')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_universidad');
        Schema::dropIfExists('question_tipo');
        Schema::dropIfExists('questions');
    }
}
