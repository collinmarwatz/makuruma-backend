<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE trucks MODIFY current_status ENUM('pending','loading','in_transit','at_border','offloading','delayed','breakdown','completed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE trucks MODIFY current_status ENUM('pending','loading','in_transit','at_border','offloading','delayed','completed') DEFAULT 'pending'");
    }
};