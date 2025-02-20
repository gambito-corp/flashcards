<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFcCardCategoryTable extends Migration
{
    public function up()
    {
        Schema::create('fc_card_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fc_card_id')
                ->constrained('fc_cards')
                ->onDelete('cascade');
            $table->foreignId('fc_category_id')
                ->constrained('fc_categories')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fc_card_category');
    }
}
