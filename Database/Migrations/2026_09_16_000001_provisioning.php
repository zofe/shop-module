<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Provisioning: a service item knows where it comes from (an order assignment or a
 * subscription item), which driver provisions it and what the driver stored; a product
 * chooses its driver; an order records its shipment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            if (! Schema::hasColumn('service_items', 'origin_type')) {
                $table->nullableMorphs('origin');            // order_item_assignment | subscription_item
            }
            if (! Schema::hasColumn('service_items', 'provisioner')) {
                $table->string('provisioner', 60)->nullable();
            }
            if (! Schema::hasColumn('service_items', 'external_ref')) {
                $table->string('external_ref')->nullable();  // the driver's own reference (account id, instance…)
            }
            if (! Schema::hasColumn('service_items', 'metadata')) {
                $table->json('metadata')->nullable();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'provisioner')) {
                $table->string('provisioner', 60)->nullable();
            }
            if (! Schema::hasColumn('products', 'activation')) {
                $table->string('activation', 20)->nullable();   // automatic | manual | customer, null = config shop.provisioning.activation
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'carrier')) {
                $table->string('carrier', 60)->nullable();
            }
            if (! Schema::hasColumn('orders', 'tracking_code')) {
                $table->string('tracking_code')->nullable();
            }
            if (! Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->dropIndex(['origin_type', 'origin_id']);
        });
        Schema::table('service_items', function (Blueprint $table) {
            $table->dropColumn(['origin_type', 'origin_id', 'provisioner', 'external_ref', 'metadata']);
        });
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn(['provisioner', 'activation']));
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(['carrier', 'tracking_code', 'shipped_at']));
    }
};
