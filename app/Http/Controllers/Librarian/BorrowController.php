<?php

namespace App\Http\Controllers\Librarian;

use App\Http\Controllers\Controller;
use App\Models\Borrow;
use App\Services\BorrowWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class BorrowController extends Controller
{
    public function complete(
        Borrow $borrow,
        BorrowWorkflowService $borrowWorkflow,
    ): RedirectResponse {
        abort_unless(Gate::allows('complete', $borrow), 404);

        $borrowWorkflow->markAsReturned($borrow);

        return to_route('librarian.dashboard')
            ->with('status', 'La devolución del préstamo se ha registrado.');
    }
}
