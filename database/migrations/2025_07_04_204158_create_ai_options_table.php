<?php
// database/migrations/xxxx_xx_xx_create_ai_options_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('ai_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('ai_questions')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_correct')->default(false);
            $table->float('points')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_options');
    }
}
