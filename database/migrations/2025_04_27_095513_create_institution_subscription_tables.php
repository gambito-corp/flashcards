<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('institution_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('institution_name');
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->integer('total_licenses');
            $table->integer('used_licenses')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_subscriptions');
    }
};
