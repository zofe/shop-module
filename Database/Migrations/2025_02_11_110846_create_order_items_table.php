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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('order_id');

            $table->unsignedBigInteger('price_list_item_id')->nullable();
            $table->string("deliverable_type")->nullable();
            $table->integer('bundle_code')->nullable();
            $table->string('prd_code')->nullable();

            $table->string('name');
            $table->integer('qty')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discountRate', 10, 2)->default(0);
            $table->decimal('taxRate', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign(['order_id'])->references(['id'])->on('orders')->onDelete('CASCADE');
            $table->foreign(['price_list_item_id'])->references(['id'])->on('price_list_items')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['price_list_item_id']);
        });

        Schema::dropIfExists('order_items');
    }
};
