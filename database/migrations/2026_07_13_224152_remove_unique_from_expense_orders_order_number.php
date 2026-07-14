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
    Schema::table('expense_orders', function (Blueprint $table) {
        $table->dropUnique(['order_number']);
        $table->index('order_number');
    });
}

public function down(): void
{
    Schema::table('expense_orders', function (Blueprint $table) {
        $table->dropIndex(['order_number']);
        $table->unique('order_number');
    });
}
};
