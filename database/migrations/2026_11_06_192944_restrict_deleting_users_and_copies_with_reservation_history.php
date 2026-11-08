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
        Schema::table('reserves', function (Blueprint $table): void {
            $table->dropForeign(['copy_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('copy_id')
                ->references('id')
                ->on('copies')
                ->restrictOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reserves', function (Blueprint $table): void {
            $table->dropForeign(['copy_id']);
            $table->dropForeign(['user_id']);

            $table->foreign('copy_id')
                ->references('id')
                ->on('copies')
                ->cascadeOnDelete();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
