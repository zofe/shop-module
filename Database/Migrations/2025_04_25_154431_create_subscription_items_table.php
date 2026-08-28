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
        Schema::create('subscription_items', function (Blueprint $table) {
            $table->increments('id');
            $table->char('subscription_id', 36);

            $table->string('name');
            $table->string('prd_code')->nullable();
            $table->integer('bundle_code')->nullable();

            $table->decimal('price', 10, 2);
            $table->integer('qty')->default(1);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('taxRate', 10, 2)->default(22.00);
            $table->decimal('total', 10, 2);

            $table->nullableUuidMorphs('deliverable');


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
        Schema::dropIfExists('subscription_items');
    }
};
