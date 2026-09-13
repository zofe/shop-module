<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// How the tax of the order was computed: rate, rule and source (a resolver or a
// gateway); tax_final is false while the amount is an estimate.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->nullable()->after('tax');
            }
            if (! Schema::hasColumn('orders', 'tax_reason')) {
                $table->string('tax_reason', 40)->nullable()->after('tax_rate');
            }
            if (! Schema::hasColumn('orders', 'tax_source')) {
                $table->string('tax_source', 40)->nullable()->after('tax_reason');
            }
            if (! Schema::hasColumn('orders', 'tax_final')) {
                $table->boolean('tax_final')->default(false)->after('tax_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['tax_final', 'tax_source', 'tax_reason', 'tax_rate'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
