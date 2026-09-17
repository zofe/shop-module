<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** A licence has a key the customer can redeem (activation policy "customer"). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            if (! Schema::hasColumn('licenses', 'key')) {
                $table->string('key', 40)->nullable()->unique();
            }
        });
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'shipping_document')) {
                $table->string('shipping_document', 120)->nullable();   // DDT / delivery note reference
            }
        });
    }

    public function down(): void
    {
        Schema::table('licenses', fn (Blueprint $table) => $table->dropUnique(['key']));
        Schema::table('licenses', fn (Blueprint $table) => $table->dropColumn('key'));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn('shipping_document'));
    }
};
