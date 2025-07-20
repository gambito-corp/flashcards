<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();

            // QUIÉN
            $table->unsignedBigInteger('user_id')->nullable()
                ->index()
                ->comment('ID del usuario autenticado (null = guest)');

            $table->string('ip_address', 45)
                ->comment('IPv4/IPv6 origen del evento');

            // CUÁNDO
            $table->timestamp('occurred_at')
                ->default(now())
                ->comment('Marca exacta del evento en UTC');

            // QUÉ
            $table->string('type', 50)
                ->comment('Categoría: brute_force, sql_injection, xss, etc.');

            $table->string('severity', 20)
                ->default('warning')
                ->comment('warning | error | critical');

            $table->string('url', 2048)
                ->nullable()
                ->comment('Ruta o endpoint afectado');

            // DETALLES
            $table->text('payload')->nullable()
                ->comment('Inputs o cabeceras relevantes (JSON)');

            $table->text('user_agent')->nullable();

            // ACCIÓN TOMADA
            $table->boolean('blocked')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps(); // created_at / updated_at
        });

        // Clave foránea (opcional, ignora si manejas multi-guard)
        Schema::table('security_incidents', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
    }
};
