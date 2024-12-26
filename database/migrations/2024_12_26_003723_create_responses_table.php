<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
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

            // Opción elegida (NULL si type=range)
            $table->foreignId('option_id')
                ->nullable()
                ->constrained('options')
                ->cascadeOnUpdate()
                // ->nullOnDelete() si quieres 'set null' en caso de que la opción se borre
                ->nullOnDelete();

            // Para tipo range
            $table->decimal('response_value', 10, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responses');
    }
}
