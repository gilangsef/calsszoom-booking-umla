<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Custom Gates untuk permission khusus

        // // Booking Permissions
        // Gate::define('booking.verify.admin', function ($user) {
        //     return $user->hasPermissionTo('booking.verify.admin');
        // });

        // Gate::define('booking.verify.operational', function ($user) {
        //     return $user->hasPermissionTo('booking.verify.operational');
        // });

        // Gate::define('booking.scan.qr', function ($user) {
        //     return $user->hasPermissionTo('booking.scan.qr');
        // });

        // Gate::define('booking.return.key', function ($user) {
        //     return $user->hasPermissionTo('booking.return.key');
        // });

        // // Room Permissions
        // Gate::define('room.report.damage', function ($user) {
        //     return $user->hasPermissionTo('room.report.damage');
        // });

        // // Zoom Permissions
        // Gate::define('zoom.verify', function ($user) {
        //     return $user->hasPermissionTo('zoom.verify');
        // });

        // // Report Permissions
        // Gate::define('report.view', function ($user) {
        //     return $user->hasPermissionTo('report.view');
        // });

        // Gate::define('report.export', function ($user) {
        //     return $user->hasPermissionTo('report.export');
        // });
    }

}
