<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMpPreapprovalPlanIdToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('mp_preapproval_plan_id', 255)
                ->nullable()
                ->after('url')
                ->comment('ID del plan en Mercado Pago');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('mp_preapproval_plan_id');
        });
    }
}
