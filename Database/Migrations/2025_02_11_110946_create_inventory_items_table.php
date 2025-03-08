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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('product_id');

            $table->string('status')->default('in_stock');
            $table->string('serial_number')->unique()->nullable();

            $table->nullableMorphs('owner');

            $table->softDeletes();
            $table->timestamps();

            $table->foreign(['product_id'])->references(['id'])->on('products')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_items');
    }
};
