<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Recurring products: the billing period on cart/order lines, subscriptions with
// their schedule and who manages them (the shop, or a gateway such as Stripe),
// renewal orders linked to their subscription.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'period')) {
                $table->string('period', 10)->default('onetime')->after('price'); // onetime | monthly | yearly
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'kind')) {
                $table->string('kind', 10)->default('order')->after('status'); // order | renewal
            }
            if (! Schema::hasColumn('orders', 'subscription_id')) {
                $table->uuid('subscription_id')->nullable()->after('kind');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            // a customer without a company subscribes too
            $table->uuid('company_id')->nullable()->change();
            if (! Schema::hasColumn('subscriptions', 'shipping')) {
                $table->decimal('shipping', 10, 2)->default(0)->after('subtotal'); // the totals calculator writes it
            }
            if (! Schema::hasColumn('subscriptions', 'period')) {
                $table->string('period', 10)->default('monthly')->after('description');
            }
            if (! Schema::hasColumn('subscriptions', 'order_id')) {
                $table->uuid('order_id')->nullable()->after('price_list_id');   // the order that created it
            }
            if (! Schema::hasColumn('subscriptions', 'next_billing_at')) {
                $table->date('next_billing_at')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('subscriptions', 'ends_at')) {
                $table->date('ends_at')->nullable()->after('next_billing_at');
            }
            if (! Schema::hasColumn('subscriptions', 'managed_by')) {
                $table->string('managed_by', 20)->default('shop')->after('status');  // shop | stripe | paddle | …
            }
            if (! Schema::hasColumn('subscriptions', 'gateway_ref')) {
                $table->string('gateway_ref')->nullable()->after('managed_by');     // the gateway's subscription id
            }
        });

        Schema::table('subscription_items', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_items', 'period')) {
                $table->string('period', 10)->default('monthly')->after('price');
            }
            if (! Schema::hasColumn('subscription_items', 'price_list_item_id')) {
                $table->unsignedBigInteger('price_list_item_id')->nullable()->after('subscription_id');
            }
            if (! Schema::hasColumn('subscription_items', 'order_item_id')) {
                $table->unsignedBigInteger('order_item_id')->nullable()->after('price_list_item_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            foreach (['order_item_id', 'price_list_item_id', 'period'] as $c) {
                if (Schema::hasColumn('subscription_items', $c)) { $table->dropColumn($c); }
            }
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            foreach (['gateway_ref', 'managed_by', 'ends_at', 'next_billing_at', 'order_id', 'period', 'shipping'] as $c) {
                if (Schema::hasColumn('subscriptions', $c)) { $table->dropColumn($c); }
            }
        });
        Schema::table('orders', function (Blueprint $table) {
            foreach (['subscription_id', 'kind'] as $c) {
                if (Schema::hasColumn('orders', $c)) { $table->dropColumn($c); }
            }
        });
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'period')) { $table->dropColumn('period'); }
        });
    }
};
