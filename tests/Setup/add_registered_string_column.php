<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dummy_models', function (Blueprint $table) {
            $table->string('registered_string')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dummy_models');
    }
};
