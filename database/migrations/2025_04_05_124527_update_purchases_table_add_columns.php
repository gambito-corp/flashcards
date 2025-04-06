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
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('preaproval_id')->nullable()->after('purchased_at')->change();
            $table->string('status')->nullable()->after('preaproval_id');
            $table->string('payer_id')->nullable()->after('status');
            $table->string('external_reference')->nullable()->after('payer_id');
            $table->string('init_point')->nullable()->after('external_reference');
            $table->string('payment_method_id')->nullable()->after('init_point');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'payer_id',
                'external_reference',
                'init_point',
                'payment_method_id'
            ]);
        });
    }
};
