<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['pending', 'paid'])->default('pending')->after('total_amount');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->foreignId('paid_by')->nullable()->after('paid_at')->constrained('users');
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->decimal('percentage', 5, 2)->nullable()->after('rate');
            $table->boolean('is_flat_amount')->default(false)->after('percentage');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['status', 'paid_at', 'paid_by']);
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropColumn(['percentage', 'is_flat_amount']);
        });
    }
};