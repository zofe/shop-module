<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(0);
            $table->boolean('is_default')->default(0);


            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_lists');
    }
};
