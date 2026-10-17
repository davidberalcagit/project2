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
        Schema::table('books', function (Blueprint $table) {
            $table->unique('isbn', 'books_isbn_unique');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->unique(['user_id', 'book_id'], 'favorites_user_id_book_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasIndex('favorites', 'user_id')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropUnique('favorites_user_id_book_id_unique');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->dropUnique('books_isbn_unique');
        });
    }
};
