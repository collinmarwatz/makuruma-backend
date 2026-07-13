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
    Schema::create('trip_legs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
        $table->enum('direction', ['go', 'return']);
        $table->foreignId('client_id')->nullable()->constrained();
        $table->decimal('rate', 10, 2)->nullable();
        $table->date('eta')->nullable();
        $table->string('location')->nullable();
        $table->string('item_sn')->nullable();
        $table->text('description')->nullable();
        $table->decimal('quantity', 10, 2)->nullable();
        $table->decimal('amount', 10, 2)->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_legs');
    }
};
