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
        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('product_id');

            $table->decimal('price_onetime_customer', 10, 2)->default(0);
            $table->decimal('price_yearly_customer', 10, 2)->default(0);
            $table->decimal('price_monthly_customer', 10, 2)->default(0);

            $table->timestamps();

            $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_list_items');
    }
};
