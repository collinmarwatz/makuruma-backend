<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_code')->unique();
            $table->foreignId('truck_id')->constrained();
            $table->foreignId('go_booking_truck_id')->nullable()->constrained('booking_trucks')->nullOnDelete();
            $table->foreignId('return_booking_truck_id')->nullable()->constrained('booking_trucks')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};