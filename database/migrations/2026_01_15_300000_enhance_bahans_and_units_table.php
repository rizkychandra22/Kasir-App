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
        Schema::table('bahans', function (Blueprint $table) {
            if (!Schema::hasColumn('bahans', 'purchase_unit')) {
                $table->string('purchase_unit')->default('pcs')->after('unit');
            }
            if (!Schema::hasColumn('bahans', 'purchase_qty')) {
                $table->decimal('purchase_qty', 15, 3)->default(1)->after('purchase_unit');
            }
            if (!Schema::hasColumn('bahans', 'base_unit')) {
                $table->string('base_unit')->default('pcs')->after('purchase_qty');
            }
            if (!Schema::hasColumn('bahans', 'cost_per_base_unit')) {
                $table->decimal('cost_per_base_unit', 15, 4)->default(0)->after('price');
            }
        });

        // Modify stock data type in bahans if needed
        Schema::table('bahans', function (Blueprint $table) {
            $table->decimal('stock', 15, 3)->default(0)->change();
            $table->decimal('price', 15, 2)->default(0)->change();
        });

        Schema::table('product_bahan', function (Blueprint $table) {
            if (!Schema::hasColumn('product_bahan', 'unit')) {
                $table->string('unit')->default('pcs')->after('quantity');
            }
            $table->decimal('quantity', 15, 3)->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahans', function (Blueprint $table) {
            $table->dropColumn(['purchase_unit', 'purchase_qty', 'base_unit', 'cost_per_base_unit']);
        });

        Schema::table('product_bahan', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
