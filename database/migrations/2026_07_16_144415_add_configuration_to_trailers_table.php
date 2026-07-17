<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->enum('configuration', ['semi_trailer', 'pulling'])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trailers', function (Blueprint $table) {
            $table->dropColumn('configuration');
        });
    }
};