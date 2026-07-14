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
    Schema::create('expense_lines', function (Blueprint $table) {
        $table->id();
        $table->foreignId('expense_order_id')->constrained()->cascadeOnDelete();
        $table->string('description');
        $table->decimal('amount', 12, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_lines');
    }
};
