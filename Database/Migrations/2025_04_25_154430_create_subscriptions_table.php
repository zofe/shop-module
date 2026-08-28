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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('price_list_id');
            $table->string('description');

            $table->uuid('company_id');
            $table->uuid('user_id');

            $table->decimal('discount', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('total', 10, 2);

            $table->date('start_date');
            $table->string('status')->default('inactive');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign(['user_id'])->references(['id'])->on('users')->onDelete('CASCADE');
            $table->foreign(['company_id'])->references(['id'])->on('companies')->onDelete('CASCADE');
            $table->foreign(['price_list_id'])->references(['id'])->on('price_lists')->onDelete('CASCADE');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
