<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tipo_universidad', function (Blueprint $table) {
            $table->id();
            // La columna 'tipo_id' debe referenciar a la tabla 'tipos'
            $table->foreignId('tipo_id')->constrained('tipos')->cascadeOnDelete();
            // La columna 'universidad_id' debe referenciar a la tabla 'universidades'
            $table->foreignId('universidad_id')->constrained('universidades')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_universidad');
    }
};
