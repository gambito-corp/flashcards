<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->text('content');       // Texto de la opción
            $table->boolean('is_correct')->default(false);
            $table->decimal('points', 5, 2);  // +1, -0.25, etc.

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('options');
    }
}
