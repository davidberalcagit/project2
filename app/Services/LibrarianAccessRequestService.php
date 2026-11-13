<?php

namespace App\Services;

use App\Models\LibrarianAccessRequest;
use App\Models\Library;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LibrarianAccessRequestService
{
    public function approve(
        User $reviewer,
        LibrarianAccessRequest $accessRequest,
        ?int $libraryId = null,
    ): void {
        Gate::forUser($reviewer)->authorize('approve', $accessRequest);

        DB::transaction(function () use ($reviewer, $accessRequest, $libraryId): void {
            $lockedRequest = LibrarianAccessRequest::query()
                ->lockForUpdate()
                ->findOrFail($accessRequest->getKey());

            abort_unless($lockedRequest->status === 'pending', 409);

            if ($lockedRequest->request_type === 'new') {
                Gate::forUser($reviewer)->authorize('create', Library::class);
                $library = Library::query()->create([
                    'name' => $lockedRequest->proposed_library_name,
                    'location' => $lockedRequest->proposed_library_location,
                ]);
            } else {
                $library = $lockedRequest->requestedLibrary
                    ?? Library::query()->findOrFail($libraryId);
            }

            $lockedRequest->requestedLibrary()->associate($library);
            $lockedRequest->save();
            $lockedRequest->user->library()->associate($library);
            $lockedRequest->user->save();
            $lockedRequest->user->assignRole('Bibliotecario');
            $lockedRequest->update([
                'status' => 'approved',
                'reviewed_by' => $reviewer->getAuthIdentifier(),
                'reviewed_at' => now(),
            ]);
        });
    }

    public function reject(User $reviewer, LibrarianAccessRequest $accessRequest): void
    {
        Gate::forUser($reviewer)->authorize('reject', $accessRequest);

        DB::transaction(function () use ($reviewer, $accessRequest): void {
            $lockedRequest = LibrarianAccessRequest::query()
                ->lockForUpdate()
                ->findOrFail($accessRequest->getKey());

            abort_unless($lockedRequest->status === 'pending', 409);

            $lockedRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewer->getAuthIdentifier(),
                'reviewed_at' => now(),
            ]);
        });
    }
}
