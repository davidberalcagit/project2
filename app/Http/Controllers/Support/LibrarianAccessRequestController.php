<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveLibrarianAccessRequest as ApproveLibrarianAccessFormRequest;
use App\Models\LibrarianAccessRequest;
use App\Models\Library;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class LibrarianAccessRequestController extends Controller
{
    public function index(): View
    {
        $requests = LibrarianAccessRequest::query()
            ->with(['user', 'requestedLibrary'])
            ->where('status', 'pending')
            ->oldest()
            ->get();
        $libraries = Library::query()->orderBy('name')->get();

        return view('support.dashboard', compact('requests', 'libraries'));
    }

    public function approve(ApproveLibrarianAccessFormRequest $request, LibrarianAccessRequest $librarianAccessRequest): RedirectResponse
    {
        DB::transaction(function () use ($request, $librarianAccessRequest): void {
            $accessRequest = LibrarianAccessRequest::query()
                ->lockForUpdate()
                ->findOrFail($librarianAccessRequest->getKey());

            abort_unless($accessRequest->status === 'pending', 409);

            if ($accessRequest->request_type === 'new') {
                Gate::authorize('create', Library::class);
                $library = Library::query()->create([
                    'name' => $accessRequest->proposed_library_name,
                    'location' => $accessRequest->proposed_library_location,
                ]);
            } else {
                $library = $accessRequest->requestedLibrary
                    ?? Library::query()->findOrFail($request->integer('library_id'));
            }

            $accessRequest->requestedLibrary()->associate($library);
            $accessRequest->save();
            $accessRequest->user->library()->associate($library);
            $accessRequest->user->save();
            $accessRequest->user->assignRole('Bibliotecario');
            $accessRequest->update([
                'status' => 'approved',
                'reviewed_by' => $request->user()->getAuthIdentifier(),
                'reviewed_at' => now(),
            ]);
        });

        return to_route('support.dashboard')
            ->with('status', 'La solicitud se ha aprobado y se ha asignado el rol Bibliotecario.');
    }

    public function reject(Request $request, LibrarianAccessRequest $librarianAccessRequest): RedirectResponse
    {
        DB::transaction(function () use ($request, $librarianAccessRequest): void {
            $accessRequest = LibrarianAccessRequest::query()
                ->lockForUpdate()
                ->findOrFail($librarianAccessRequest->getKey());

            abort_unless($accessRequest->status === 'pending', 409);

            $accessRequest->update([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->getAuthIdentifier(),
                'reviewed_at' => now(),
            ]);
        });

        return to_route('support.dashboard')
            ->with('status', 'La solicitud se ha rechazado.');
    }
}
