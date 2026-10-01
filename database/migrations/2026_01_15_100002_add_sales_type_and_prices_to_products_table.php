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
        Schema::table('products', function (Blueprint $table) {
            $table->enum('sales_type', ['online', 'offline', 'all'])->default('all')->after('description_prd');
            $table->integer('price_online')->nullable()->after('sales_type');
            $table->integer('price_offline')->nullable()->after('price_online');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sales_type', 'price_online', 'price_offline']);
        });
    }
};
