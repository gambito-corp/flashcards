<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // Nombre de la suscripción
            $table->decimal('price', 10, 2);       // Precio
            $table->integer('duration_days');    // Duración en días (30, 180, 365)
            $table->text('description')->nullable(); // Descripción opcional
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
