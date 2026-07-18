<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expense_lines', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->nullable()->after('description');
            $table->decimal('unit_rate', 10, 2)->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('expense_lines', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'unit_rate']);
        });
    }
};