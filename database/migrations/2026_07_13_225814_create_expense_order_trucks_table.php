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
    Schema::create('expense_order_trucks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('expense_order_id')->constrained()->cascadeOnDelete();
        $table->foreignId('truck_id')->constrained();
        $table->timestamps();

        $table->unique(['expense_order_id', 'truck_id']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_order_trucks');
    }
};
