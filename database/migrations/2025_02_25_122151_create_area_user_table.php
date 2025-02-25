<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAreaUserTable extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up()
    {
        Schema::create('area_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('area_id');
            $table->timestamps();

            // Llaves foráneas
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');

            // Restricción única para evitar duplicados
            $table->unique(['user_id', 'area_id']);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down()
    {
        Schema::dropIfExists('area_user');
    }
}
