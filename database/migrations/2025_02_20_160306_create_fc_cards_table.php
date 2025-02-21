<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFcCardsTable extends Migration
{
    public function up()
    {
        Schema::create('fc_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->text('pregunta');
            $table->string('url')->nullable();
            $table->string('imagen')->nullable();
            $table->text('respuesta');
            $table->string('url_respuesta')->nullable();
            $table->string('imagen_respuesta')->nullable();
            $table->unsignedBigInteger('errors')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fc_cards');
    }
}
