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
        Schema::table('librarian_access_requests', function (Blueprint $table) {
            $table->string('request_type')->default('existing')->after('status');
            $table->foreignId('requested_library_id')
                ->nullable()
                ->after('request_type')
                ->constrained('libraries')
                ->nullOnDelete();
            $table->string('proposed_library_name')->nullable()->after('requested_library_id');
            $table->string('proposed_library_location')->nullable()->after('proposed_library_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('librarian_access_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requested_library_id');
            $table->dropColumn([
                'request_type',
                'proposed_library_name',
                'proposed_library_location',
            ]);
        });
    }
};
