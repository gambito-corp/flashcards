<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFcCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('fc_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained() // Asume la tabla "users"
                ->onDelete('cascade');
            $table->string('nombre');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fc_categories');
    }
}
