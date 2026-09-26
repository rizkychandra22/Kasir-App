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
        Schema::create('bahan_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_id')->constrained('bahans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment'])->default('in');
            $table->decimal('qty', 10, 2);
            $table->decimal('stock_before', 10, 2);
            $table->decimal('stock_after', 10, 2);
            $table->string('reference')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bahan_stock_movements');
    }
};
