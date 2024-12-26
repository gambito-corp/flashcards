<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamTeamTable extends Migration
{
    public function up()
    {
        Schema::create('exam_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')
                ->constrained('teams')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('exam_id')
                ->constrained('exams')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_team');
    }
}
