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
        $supportedStatuses = [
            'available' => 'Available',
            'reserved' => 'Reserved',
            'borrowed' => 'Borrowed',
            'expired' => 'Expired',
            'unavailable' => 'Unavailable',
        ];
        $groups = DB::table('copy_statuses')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->groupBy(fn (object $status): string => strtolower(trim($status->name)));

        foreach ($groups as $normalizedName => $statuses) {
            $canonicalStatus = $statuses->first();
            $canonicalName = $supportedStatuses[$normalizedName] ?? trim($canonicalStatus->name);

            foreach ($statuses->skip(1) as $duplicateStatus) {
                DB::table('copies')
                    ->where('copy_status_id', $duplicateStatus->id)
                    ->update(['copy_status_id' => $canonicalStatus->id]);

                DB::table('copy_statuses')
                    ->where('id', $duplicateStatus->id)
                    ->delete();
            }

            DB::table('copy_statuses')
                ->where('id', $canonicalStatus->id)
                ->update(['name' => $canonicalName]);
        }

        Schema::table('copy_statuses', function (Blueprint $table): void {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('copy_statuses', function (Blueprint $table): void {
            $table->dropUnique(['name']);
        });
    }
};
