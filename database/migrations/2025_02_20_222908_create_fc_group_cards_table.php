<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFcGroupCardsTable extends Migration
{
    public function up()
    {
        Schema::create('fc_group_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->unsignedInteger('correct')->default(0);
            $table->unsignedInteger('incorrect')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fc_group_cards');
    }
}
