<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveLibrarianAccessRequest as ApproveLibrarianAccessFormRequest;
use App\Models\LibrarianAccessRequest;
use App\Models\Library;
use App\Services\LibrarianAccessRequestService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LibrarianAccessRequestController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', LibrarianAccessRequest::class);

        $requests = LibrarianAccessRequest::query()
            ->with(['user', 'requestedLibrary'])
            ->where('status', 'pending')
            ->oldest()
            ->get();
        $libraries = Library::query()->orderBy('name')->get();

        return view('support.dashboard', compact('requests', 'libraries'));
    }

    public function approve(
        ApproveLibrarianAccessFormRequest $request,
        LibrarianAccessRequest $librarianAccessRequest,
        LibrarianAccessRequestService $accessRequests,
    ): RedirectResponse {
        $accessRequests->approve(
            $request->user(),
            $librarianAccessRequest,
            $request->integer('library_id') ?: null,
        );

        return to_route('support.dashboard')
            ->with('status', 'La solicitud se ha aprobado y se ha asignado el rol Bibliotecario.');
    }

    public function reject(
        Request $request,
        LibrarianAccessRequest $librarianAccessRequest,
        LibrarianAccessRequestService $accessRequests,
    ): RedirectResponse {
        $accessRequests->reject($request->user(), $librarianAccessRequest);

        return to_route('support.dashboard')
            ->with('status', 'La solicitud se ha rechazado.');
    }
}
