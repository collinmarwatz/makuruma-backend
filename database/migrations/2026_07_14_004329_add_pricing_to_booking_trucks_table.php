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
    Schema::table('booking_trucks', function (Blueprint $table) {
        $table->decimal('rate', 10, 2)->nullable()->after('invoiced_detention_charge');
        $table->decimal('quantity', 10, 2)->nullable()->after('rate');
        $table->decimal('amount', 12, 2)->nullable()->after('quantity');
    });
}

public function down(): void
{
    Schema::table('booking_trucks', function (Blueprint $table) {
        $table->dropColumn(['rate', 'quantity', 'amount']);
    });
}
};
