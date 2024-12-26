<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionReportsTable extends Migration
{
    public function up()
    {
        Schema::create('question_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('option_id')
                ->nullable()
                ->constrained('options')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->text('description');
            $table->enum('status', ['pending','review','response','close'])
                ->default('pending');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_reports');
    }
}
