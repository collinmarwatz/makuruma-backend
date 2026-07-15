<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expense_lines', function (Blueprint $table) {
            $table->enum('line_category', ['fuel', 'vibali_tunduma', 'vibali_congo', 'mengine'])->default('mengine')->after('expense_order_id');
            $table->foreignId('vendor_id')->nullable()->after('line_category')->constrained();
            $table->enum('currency', ['TZS', 'USD', 'ZMK'])->default('TZS')->after('description');
            $table->decimal('exchange_rate', 12, 4)->default(1)->after('currency');
            $table->decimal('original_amount', 12, 2)->nullable()->after('exchange_rate');
        });
    }

    public function down(): void
    {
        Schema::table('expense_lines', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['line_category', 'vendor_id', 'currency', 'exchange_rate', 'original_amount']);
        });
    }
};
