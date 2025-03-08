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
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('price_list_id');

            $table->uuid('user_id')->nullable();
            $table->uuid('company_id')->nullable();

            $table->decimal('discount', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('shipping', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('new');
            $table->longText('note')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign(['price_list_id'])->references(['id'])->on('price_lists')->onDelete('CASCADE');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onDelete('CASCADE');
            $table->foreign(['company_id'])->references(['id'])->on('companies')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
