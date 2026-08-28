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
        Schema::create('subscription_items_expiring', function (Blueprint $table) {
            $table->increments('id');
            $table->char('subscription_id', 36)->nullable();

            $table->string('model_type')->nullable();
            $table->uuid('model_id')->nullable();

            $table->dateTime('expire_date');
            $table->softDeletes();
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
        Schema::dropIfExists('subscription_items_expiring');
    }
};
