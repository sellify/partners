<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_setting', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->bigInteger('user_id', false, true)->unsigned();
            $table->bigInteger('setting_id', false, true)->unsigned();
            $table->longText('value')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'setting_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_setting');
    }
}
