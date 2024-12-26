<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamQuestionTable extends Migration
{
    public function up()
    {
        Schema::create('exam_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_question');
    }
}
