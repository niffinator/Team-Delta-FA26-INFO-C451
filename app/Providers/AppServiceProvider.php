<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        $isLibraryStaff = function (User $user): bool {
            return in_array($user->role, ['Clerk', 'Librarian'], true);
        };

        $isLibrarian = function (User $user): bool {
            return $user->role === 'Librarian';
        };

        // All-staff functionality
        Gate::define('access-library', $isLibraryStaff);
        Gate::define('process-circulation', $isLibraryStaff);
        Gate::define('run-reports', $isLibraryStaff);

        // Library-only functionality
        Gate::define('manage-books', $isLibrarian);
        Gate::define('manage-members', $isLibrarian);
        Gate::define('import-data', $isLibrarian);
        Gate::define('export-data', $isLibrarian);
    }
}
