<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The price list says how a product is sold: one-time (the cart), with an activation
 * fee, as a monthly / yearly fee (a subscription), after a trial; a row may target a
 * variant. Price lists per role (companies.pricelist_id for exceptions). Bundles are
 * products made of components. Subscriptions carry their period and schedule.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            if (! Schema::hasColumn('price_lists', 'role')) {
                $table->string('role', 40)->nullable()->after('name');   // customer | partner | reseller… null = the default list
            }
        });

        Schema::table('price_list_items', function (Blueprint $table) {
            foreach ([
                'product_variant_id'  => fn () => $table->unsignedBigInteger('product_variant_id')->nullable()->after('product_id'),
                'has_onetime_payment' => fn () => $table->boolean('has_onetime_payment')->default(false),
                'price_onetime'       => fn () => $table->decimal('price_onetime', 10, 2)->default(0),
                'has_activation_price' => fn () => $table->boolean('has_activation_price')->default(false),
                'price_activation'    => fn () => $table->decimal('price_activation', 10, 2)->default(0),
                'fee_canbe_monthly'   => fn () => $table->boolean('fee_canbe_monthly')->default(false),
                'fee_monthly'         => fn () => $table->decimal('fee_monthly', 10, 2)->default(0),
                'fee_canbe_yearly'    => fn () => $table->boolean('fee_canbe_yearly')->default(false),
                'fee_yearly'          => fn () => $table->decimal('fee_yearly', 10, 2)->default(0),
                'trial_days'          => fn () => $table->unsignedInteger('trial_days')->default(0),
            ] as $column => $add) {
                if (! Schema::hasColumn('price_list_items', $column)) {
                    $add();
                }
            }
        });

        // the three *_customer columns of 2.x become the explicit flags + prices
        if (Schema::hasColumn('price_list_items', 'price_onetime_customer')) {
            foreach (DB::table('price_list_items')->get() as $row) {
                DB::table('price_list_items')->where('id', $row->id)->update([
                    'has_onetime_payment' => $row->price_onetime_customer > 0,
                    'price_onetime'       => $row->price_onetime_customer,
                    'fee_canbe_monthly'   => $row->price_monthly_customer > 0,
                    'fee_monthly'         => $row->price_monthly_customer,
                    'fee_canbe_yearly'    => $row->price_yearly_customer > 0,
                    'fee_yearly'          => $row->price_yearly_customer,
                ]);
            }
            Schema::table('price_list_items', function (Blueprint $table) {
                $table->dropColumn(['price_onetime_customer', 'price_yearly_customer', 'price_monthly_customer']);
            });
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('type', 30)->change();   // inventory_item | service_item | bundle (was an enum)
        });

        if (! Schema::hasTable('product_bundle_items')) {
            Schema::create('product_bundle_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bundle_product_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('product_variant_id')->nullable();
                $table->unsignedInteger('qty')->default(1);
                $table->timestamps();
                $table->foreign('bundle_product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('companies') && ! Schema::hasColumn('companies', 'pricelist_id')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->unsignedBigInteger('pricelist_id')->nullable();   // a price list of its own, else the role's / the default
            });
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->unsignedBigInteger('product_variant_id')->nullable()->after('price_list_item_id');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->uuid('company_id')->nullable()->change();   // a customer without a company subscribes too
            foreach ([
                'shipping'        => fn () => $table->decimal('shipping', 10, 2)->default(0)->after('subtotal'),
                'period'          => fn () => $table->string('period', 10)->default('monthly')->after('description'),
                'next_billing_at' => fn () => $table->date('next_billing_at')->nullable()->after('start_date'),
                'trial_ends_at'   => fn () => $table->date('trial_ends_at')->nullable()->after('next_billing_at'),
                'ends_at'         => fn () => $table->date('ends_at')->nullable()->after('trial_ends_at'),
                'gateway'         => fn () => $table->string('gateway', 40)->nullable()->after('status'),      // who collects: manual, stripe…
                'gateway_ref'     => fn () => $table->string('gateway_ref')->nullable()->after('gateway'),    // mandate / customer / subscription id there
            ] as $column => $add) {
                if (! Schema::hasColumn('subscriptions', $column)) {
                    $add();
                }
            }
        });

        Schema::table('subscription_items', function (Blueprint $table) {
            foreach ([
                'price_list_item_id' => fn () => $table->unsignedBigInteger('price_list_item_id')->nullable()->after('subscription_id'),
                'product_variant_id' => fn () => $table->unsignedBigInteger('product_variant_id')->nullable()->after('price_list_item_id'),
                'period'             => fn () => $table->string('period', 10)->default('monthly')->after('price'),
            ] as $column => $add) {
                if (! Schema::hasColumn('subscription_items', $column)) {
                    $add();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            foreach (['period', 'product_variant_id', 'price_list_item_id'] as $c) {
                if (Schema::hasColumn('subscription_items', $c)) { $table->dropColumn($c); }
            }
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            foreach (['gateway_ref', 'gateway', 'ends_at', 'trial_ends_at', 'next_billing_at', 'period', 'shipping'] as $c) {
                if (Schema::hasColumn('subscriptions', $c)) { $table->dropColumn($c); }
            }
        });
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_variant_id')) { $table->dropColumn('product_variant_id'); }
        });
        if (Schema::hasTable('companies') && Schema::hasColumn('companies', 'pricelist_id')) {
            Schema::table('companies', fn (Blueprint $table) => $table->dropColumn('pricelist_id'));
        }
        Schema::dropIfExists('product_bundle_items');
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->decimal('price_onetime_customer', 10, 2)->default(0);
            $table->decimal('price_yearly_customer', 10, 2)->default(0);
            $table->decimal('price_monthly_customer', 10, 2)->default(0);
        });
        foreach (DB::table('price_list_items')->get() as $row) {
            DB::table('price_list_items')->where('id', $row->id)->update([
                'price_onetime_customer' => $row->price_onetime, 'price_monthly_customer' => $row->fee_monthly, 'price_yearly_customer' => $row->fee_yearly,
            ]);
        }
        Schema::table('price_list_items', function (Blueprint $table) {
            $table->dropColumn(['product_variant_id', 'has_onetime_payment', 'price_onetime', 'has_activation_price', 'price_activation',
                'fee_canbe_monthly', 'fee_monthly', 'fee_canbe_yearly', 'fee_yearly', 'trial_days']);
        });
        Schema::table('price_lists', function (Blueprint $table) {
            if (Schema::hasColumn('price_lists', 'role')) { $table->dropColumn('role'); }
        });
    }
};
