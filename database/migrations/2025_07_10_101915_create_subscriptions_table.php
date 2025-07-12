<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// database/migrations/2025_07_10_000000_create_subscriptions_table.php
return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mercadopago_id')->unique();
            $table->string('preapproval_plan_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('init_point')->nullable();
            $table->unsignedTinyInteger('frequency')->nullable();
            $table->string('frequency_type')->nullable();   // days | months
            $table->decimal('transaction_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

