<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('office_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['furniture', 'electronics', 'equipment', 'vehicle', 'other'])->default('other');
            $table->string('serial_number')->nullable();
            $table->decimal('buying_price', 14, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('location')->nullable();
            $table->enum('condition', ['active', 'under_repair', 'disposed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_assets');
    }
};
