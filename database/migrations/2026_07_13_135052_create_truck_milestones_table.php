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
    Schema::create('truck_milestones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('booking_truck_id')->constrained()->cascadeOnDelete();
        $table->foreignId('checkpoint_id')->constrained();
        $table->dateTime('arrival_at')->nullable();
        $table->dateTime('dispatch_at')->nullable();
        $table->timestamps();

        $table->unique(['booking_truck_id', 'checkpoint_id']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('truck_milestones');
    }
};
