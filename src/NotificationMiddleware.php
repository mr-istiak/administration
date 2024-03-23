<?php

namespace Administration;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Inertia::share('success', session('success'));
        Inertia::share('warning', session('warning'));
        Inertia::share('info', session('info'));
        return $next($request);
    }
}
