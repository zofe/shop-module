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
        Schema::create('subscription_starlings', function (Blueprint $table) {
            $table->increments('id');
            $table->char('subscription_id', 36);
            $table->date('starling_date')->comment('data dello storno');
            $table->double('price', 8, 2);
            $table->double('subtotal', 8, 2);
            $table->double('taxRate', 8, 2)->default(22.00);
            $table->double('total', 8, 2);

            $table->decimal('coin_price', 12, 0);
            $table->decimal('coin_subtotal', 12, 0);
            $table->decimal('coin_total', 12, 0);

            $table->string('model_type')->nullable();
            $table->uuid('model_id')->nullable();

            $table->unsignedInteger('product_id')->nullable();
            $table->string('service_model_type')->nullable();
            $table->json('service_model_ids')->nullable();

            $table->longText('name');
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
        Schema::dropIfExists('subscription_starlings');
    }
};
