<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bahan_stock_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('bahan_stock_movements', 'cost_per_base_unit')) {
                $table->decimal('cost_per_base_unit', 15, 4)->nullable()->default(0)->after('stock_after');
            }
            if (!Schema::hasColumn('bahan_stock_movements', 'total_cost')) {
                $table->decimal('total_cost', 15, 2)->nullable()->default(0)->after('cost_per_base_unit');
            }
        });

        Schema::table('shopping_details', function (Blueprint $table) {
            if (!Schema::hasColumn('shopping_details', 'material_cost')) {
                $table->decimal('material_cost', 15, 2)->nullable()->default(0)->after('subtotal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_stock_movements', function (Blueprint $table) {
            $table->dropColumn(['cost_per_base_unit', 'total_cost']);
        });

        Schema::table('shopping_details', function (Blueprint $table) {
            $table->dropColumn('material_cost');
        });
    }
};
