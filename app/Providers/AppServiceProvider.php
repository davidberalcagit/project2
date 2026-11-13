<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Copie;
use App\Models\LibrarianAccessRequest;
use App\Models\Library;
use App\Policies\BookPolicy;
use App\Policies\BorrowPolicy;
use App\Policies\CopiePolicy;
use App\Policies\LibrarianAccessRequestPolicy;
use App\Policies\LibraryPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Book::class, BookPolicy::class);
        Gate::policy(Borrow::class, BorrowPolicy::class);
        Gate::policy(Copie::class, CopiePolicy::class);
        Gate::policy(LibrarianAccessRequest::class, LibrarianAccessRequestPolicy::class);
        Gate::policy(Library::class, LibraryPolicy::class);

        RateLimiter::for('login', function (Request $request): Limit {
            $email = Str::lower((string) $request->input('email', ''));

            return Limit::perMinute(5)
                ->by(Str::transliterate($email.'|'.$request->ip()));
        });
    }
}
