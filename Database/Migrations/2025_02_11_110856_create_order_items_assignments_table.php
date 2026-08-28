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
        Schema::create('order_items_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_item_id');

            $table->string('serial_number')->nullable();
            $table->json('metadata')->nullable();

            $table->nullableUuidMorphs('deliverable');

            $table->uuid('license_id')->nullable();
            $table->uuid('invoice_id')->nullable();
            $table->string('status', 20)->default('pending');

            $table->double('subtotal', 8, 2)->default(0);
            $table->double('tax', 8, 2)->default(0);
            $table->double('total', 8, 2)->default(0);

            $table->timestamps();

            $table->foreign('order_item_id')
                ->references('id')
                ->on('order_items')
                ->onDelete('CASCADE');


            $table->foreign('license_id')
                ->references('id')->on('licenses')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items_assignments');
    }
};
