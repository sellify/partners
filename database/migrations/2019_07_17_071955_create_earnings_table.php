<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEarningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('earnings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('app_id')->nullable()->unsigned();
            $table->bigInteger('shop_id')->unsigned();
            $table->integer('amount');
            $table->timestamp('charge_created_at')->nullable();
            $table->string('charge_type');
            $table->string('category');
            $table->string('theme_name')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('payout_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('earnings');
    }
}
