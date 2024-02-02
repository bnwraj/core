<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->text('value');
            $table->enum('type', ['string', 'text', 'json', 'numeric', 'boolean']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
