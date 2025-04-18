<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('medisearch_questions', function (Blueprint $table) {
            $table->string('model')->nullable()->after('chat_id');
        });
    }

    public function down()
    {
        Schema::table('medisearch_questions', function (Blueprint $table) {
            $table->dropColumn('model');
        });
    }
};
