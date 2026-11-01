<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLibrarianAccessRequest;
use App\Models\Library;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LibrarianAccessRequestController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $accessRequest = $request->user()->librarianAccessRequest;

        if ($request->user()->can('access-library') || $accessRequest?->status === 'pending') {
            return to_route('dashboard');
        }

        $libraries = Library::query()->orderBy('name')->get();

        return view('librarian-access-requests.create', compact('libraries'));
    }

    public function store(StoreLibrarianAccessRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userId = $request->user()->getKey();

        DB::transaction(function () use ($validated, $userId): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $accessRequest = $user->librarianAccessRequest()->first();

            abort_unless(
                ! $user->can('access-library')
                && (! $accessRequest || $accessRequest->status === 'rejected'),
                403
            );

            $attributes = [
                'request_type' => $validated['request_type'],
                'requested_library_id' => $validated['library_id'] ?? null,
                'proposed_library_name' => $validated['proposed_library_name'] ?? null,
                'proposed_library_location' => $validated['proposed_library_location'] ?? null,
                'status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ];

            if ($accessRequest) {
                $accessRequest->update($attributes);

                return;
            }

            $user->librarianAccessRequest()->create($attributes);
        });

        return to_route('dashboard')
            ->with('status', 'Tu solicitud de acceso como bibliotecario se ha enviado a Soporte.');
    }
}
