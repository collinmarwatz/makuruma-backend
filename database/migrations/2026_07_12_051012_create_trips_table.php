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
    Schema::create('trips', function (Blueprint $table) {
        $table->id();
        $table->string('trip_number')->unique();
        $table->foreignId('truck_id')->constrained();
        $table->foreignId('trailer_id')->nullable()->constrained();
        $table->foreignId('driver_id')->nullable()->constrained();
        $table->foreignId('convoy_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('capacity_override', 8, 2)->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
