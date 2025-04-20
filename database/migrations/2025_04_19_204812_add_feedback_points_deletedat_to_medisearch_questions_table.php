<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medisearch_questions', function (Blueprint $table) {
            $table->text('feedback')->nullable()->after('response');
            $table->integer('points')->nullable()->after('feedback');
            $table->dateTime('deleted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('medisearch_questions', function (Blueprint $table) {
            $table->dropColumn(['feedback', 'points', 'deleted_at']);
        });
    }

};
