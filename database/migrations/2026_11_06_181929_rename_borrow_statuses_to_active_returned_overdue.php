<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $legacyStatusIds = DB::table('borrow_statuses')
            ->whereIn('name', ['Borrowed', 'Available', 'Expired', 'Reserved', 'Unavailable'])
            ->pluck('id', 'name')
            ->all();
        $statusNames = [
            'Active' => ['Borrowed', 'Active'],
            'Returned' => ['Available', 'Returned'],
            'Overdue' => ['Expired', 'Overdue'],
        ];
        $statusIds = [];

        foreach ($statusNames as $newName => $oldNames) {
            $status = DB::table('borrow_statuses')
                ->whereIn('name', $oldNames)
                ->orderByRaw('CASE name WHEN ? THEN 0 ELSE 1 END', [$oldNames[0]])
                ->first();

            if ($status === null) {
                $statusIds[$newName] = DB::table('borrow_statuses')->insertGetId([
                    'name' => $newName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                continue;
            }

            $statusIds[$newName] = $status->id;

            DB::table('borrow_statuses')
                ->where('id', $status->id)
                ->update([
                    'name' => $newName,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasColumn('borrows', 'returned_at')) {
            DB::table('borrows')
                ->whereNotNull('returned_at')
                ->update(['borrow_status_id' => $statusIds['Returned']]);

            DB::table('borrows')
                ->whereNull('returned_at')
                ->whereDate('deadline', '<', today()->toDateString())
                ->update(['borrow_status_id' => $statusIds['Overdue']]);

            DB::table('borrows')
                ->whereNull('returned_at')
                ->whereDate('deadline', '>=', today()->toDateString())
                ->update(['borrow_status_id' => $statusIds['Active']]);
        } else {
            foreach ([
                'Borrowed' => 'Active',
                'Available' => 'Returned',
                'Expired' => 'Overdue',
            ] as $legacyName => $newName) {
                if (isset($legacyStatusIds[$legacyName])) {
                    DB::table('borrows')
                        ->where('borrow_status_id', $legacyStatusIds[$legacyName])
                        ->update(['borrow_status_id' => $statusIds[$newName]]);
                }
            }

            $knownLegacyIds = array_values(array_intersect_key(
                $legacyStatusIds,
                array_flip(['Borrowed', 'Available', 'Expired']),
            ));

            if ($knownLegacyIds !== []) {
                DB::table('borrows')
                    ->whereIn('borrow_status_id', array_values($legacyStatusIds))
                    ->whereNotIn('borrow_status_id', $knownLegacyIds)
                    ->update(['borrow_status_id' => $statusIds['Active']]);
            }
        }

        DB::table('borrow_statuses')
            ->whereNotIn('id', array_values($statusIds))
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $legacyStatuses = [
            'Borrowed' => 'Active',
            'Available' => 'Returned',
            'Expired' => 'Overdue',
        ];

        foreach ($legacyStatuses as $legacyName => $currentName) {
            DB::table('borrow_statuses')
                ->where('name', $currentName)
                ->update([
                    'name' => $legacyName,
                    'updated_at' => now(),
                ]);
        }

        foreach (['Reserved', 'Unavailable'] as $name) {
            if (! DB::table('borrow_statuses')->where('name', $name)->exists()) {
                DB::table('borrow_statuses')->insert([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
