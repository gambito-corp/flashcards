<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /*
             * Marca de bloqueo de la cuenta (NULL = no bloqueado).
             * Lo colocamos inmediatamente después de admin_attempts.
             */
            $table->timestamp('blocked_at')
                ->nullable()
                ->after('admin_attempts');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('blocked_at');
        });
    }
};
