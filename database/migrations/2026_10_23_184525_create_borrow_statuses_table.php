<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('borrow_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        foreach (['Available', 'Reserved', 'Borrowed', 'Expired', 'Unavailable'] as $name) {
            DB::table('borrow_statuses')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $borrowedStatusId = DB::table('borrow_statuses')->where('name', 'Borrowed')->value('id');

        Schema::table('borrows', function (Blueprint $table) use ($borrowedStatusId) {
            $table->foreignId('borrow_status_id')
                ->default($borrowedStatusId)
                ->constrained('borrow_statuses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropConstrainedForeignId('borrow_status_id');
        });

        Schema::dropIfExists('borrow_statuses');
    }
};
