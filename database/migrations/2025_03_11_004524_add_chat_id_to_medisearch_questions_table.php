<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChatIdToMedisearchQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('medisearch_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_id')->nullable()->after('user_id');
            $table->foreign('chat_id')->references('id')->on('medisearch_chats')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('medisearch_questions', function (Blueprint $table) {
            $table->dropForeign(['chat_id']);
            $table->dropColumn('chat_id');
        });
    }
}
