<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->enum('invoice_type', ['advance', 'settlement', 'standing_time', 'adjustment']);
            $table->foreignId('booking_id')->constrained();
            $table->foreignId('client_id')->constrained();
            $table->date('invoice_date');
            $table->string('mode_of_payment')->nullable();
            $table->string('delivery_note_no')->nullable();
            $table->date('delivery_note_date')->nullable();
            $table->string('supplier_ref')->nullable();
            $table->string('other_ref')->nullable();
            $table->string('loading_con_no')->nullable();
            $table->string('settlement_no')->nullable();
            $table->string('dispatched_through')->nullable();
            $table->string('destination')->nullable();
            $table->string('terms_of_delivery')->nullable();
            $table->string('proof_of_delivery_path')->nullable();
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};