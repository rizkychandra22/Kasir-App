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
        Schema::table('shoppings', function (Blueprint $table) {
            $table->enum('sales_type', ['online', 'offline'])->default('offline')->after('invoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shoppings', function (Blueprint $table) {
            $table->dropColumn('sales_type');
        });
    }
};
