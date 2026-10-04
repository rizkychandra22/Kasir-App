<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('target_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('target_sales', 'year')) {
                $table->integer('year')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('target_sales', 'annual_sales_target')) {
                $table->decimal('annual_sales_target', 15, 2)->default(0.00)->after('year');
            }
            if (!Schema::hasColumn('target_sales', 'average_selling_price')) {
                $table->decimal('average_selling_price', 15, 2)->default(0.00)->after('annual_sales_target');
            }
            if (!Schema::hasColumn('target_sales', 'annual_target_cups')) {
                $table->decimal('annual_target_cups', 15, 2)->default(0.00)->after('average_selling_price');
            }
            if (!Schema::hasColumn('target_sales', 'monthly_sales_target')) {
                $table->decimal('monthly_sales_target', 15, 2)->default(0.00)->after('annual_target_cups');
            }
            if (!Schema::hasColumn('target_sales', 'monthly_target_cups')) {
                $table->decimal('monthly_target_cups', 15, 2)->default(0.00)->after('monthly_sales_target');
            }
        });

        // Populate existing records with defaults if any
        $currentYear = (int)date('Y');
        DB::table('target_sales')->whereNull('year')->update([
            'year' => $currentYear,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_sales', function (Blueprint $table) {
            $table->dropColumn([
                'year',
                'annual_sales_target',
                'average_selling_price',
                'annual_target_cups',
                'monthly_sales_target',
                'monthly_target_cups',
            ]);
        });
    }
};
