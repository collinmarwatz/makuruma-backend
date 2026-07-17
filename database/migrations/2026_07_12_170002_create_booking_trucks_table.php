<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_trucks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable();
            $table->foreignId('truck_id')->constrained();
            $table->foreignId('trailer_id')->nullable()->constrained();
            $table->foreignId('driver_id')->nullable()->constrained();
            $table->decimal('capacity', 8, 2)->nullable();
            $table->string('cargo')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->date('loading_point_arrival_date')->nullable();
            $table->date('loading_date')->nullable();
            $table->date('loading_dispatch_date')->nullable();
            $table->date('offloading_point_arrival_date')->nullable();
            $table->date('offloading_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_trucks');
    }
};