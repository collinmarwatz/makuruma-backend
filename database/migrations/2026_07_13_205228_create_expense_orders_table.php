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
    Schema::create('expense_orders', function (Blueprint $table) {
        $table->id();
        $table->string('order_number')->unique();
        $table->enum('category', ['trip', 'office', 'truck']);
        $table->foreignId('trip_id')->nullable()->constrained();
        $table->foreignId('truck_id')->nullable()->constrained();
        $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
        $table->foreignId('created_by')->constrained('users');
        $table->foreignId('approved_by')->nullable()->constrained('users');
        $table->dateTime('approved_at')->nullable();
        $table->foreignId('paid_by')->nullable()->constrained('users');
        $table->dateTime('paid_at')->nullable();
        $table->decimal('total_amount', 12, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_orders');
    }
};
