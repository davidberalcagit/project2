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
        Schema::table('copies', function (Blueprint $table) {
            $table->foreignId('copy_status_id')
                ->nullable()
                ->constrained('copy_statuses')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('copies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('copy_status_id');
        });
    }
};
