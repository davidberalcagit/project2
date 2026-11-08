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

        foreach (['Active', 'Returned', 'Overdue'] as $name) {
            DB::table('borrow_statuses')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $activeStatusId = DB::table('borrow_statuses')->where('name', 'Active')->value('id');

        Schema::table('borrows', function (Blueprint $table) use ($activeStatusId) {
            $table->foreignId('borrow_status_id')
                ->default($activeStatusId)
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
