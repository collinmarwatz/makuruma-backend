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
    Schema::create('invoice_lines', function (Blueprint $table) {
        $table->id();
        $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
        $table->foreignId('trip_leg_id')->nullable()->constrained()->nullOnDelete();
        $table->string('description');
        $table->decimal('quantity', 10, 2)->nullable();
        $table->decimal('rate', 10, 2)->nullable();
        $table->decimal('amount', 12, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
