<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFcCardsGroupCardsTable extends Migration
{
    public function up()
    {
        Schema::create('fc_cards_group_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fc_group_card_id')
                ->constrained('fc_group_cards')
                ->onDelete('cascade');
            $table->foreignId('fc_card_id')
                ->constrained('fc_cards')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fc_cards_group_cards');
    }
}
