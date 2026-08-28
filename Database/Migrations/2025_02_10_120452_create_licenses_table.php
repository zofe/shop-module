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
        Schema::create('licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('next_license_id', 36)->nullable();

            $table->unsignedInteger('product_id');
            $table->nullableUuidMorphs('deliverable');

            $table->string('status')->default('inactive');

            $table->unsignedInteger('duration')->default(12);
            $table->date('activation_date')->nullable();
            $table->date('expire_date')->nullable();

            $table->nullableMorphs('owner');
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
        Schema::dropIfExists('licenses');
    }
};
