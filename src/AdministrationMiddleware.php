<?php

namespace Administration;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use function PHPUnit\Framework\returnSelf;

class AdministrationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Inertia::share('success', session('success'));
        Inertia::share('warning', session('warning'));
        Inertia::share('info', session('info'));

        if(!Auth::check()) return $next($request);
        if($request->route()->uri === '/') return $next($request);

        $role = Role::user($request->user());
        $permissionType = $role->permissions;
        $permissions = $role->getAttribute($permissionType);
        if(($permissionType === 'exclude') && (count($permissions) <= 0)) return $next($request);
        $routes = [];
        $middlewares = [];
        foreach ($permissions as $permisstion) {
            if(explode(':', $permisstion)[0] == 'route') $routes[] = explode(':', $permisstion)[1].'*';
            if(explode(':', $permisstion)[0] == 'middleware') $middlewares[] = explode(':', $permisstion)[1];
        }

        if(
            (($permissionType === 'exclude') && $request->route()->named($routes)) ||
            (($permissionType === 'include') && !$request->route()->named($routes))
        ) return redirect('/', 301);

        $currentMiddlewares = array_flip($request->route()->computedMiddleware);
        $middlewarePresent = false;
        foreach ($middlewares as $middlewares) {
            (function() use(&$middlewarePresent, &$middlewares, &$currentMiddlewares) {
                if($middlewarePresent) return;
                $middlewarePresent = isset($currentMiddlewares[$middlewares]);
            })();
        }
        if(
            (($permissionType === 'exclude') && $middlewarePresent) ||
            (($permissionType === 'include') && !$middlewarePresent)
        ) return redirect('/', 301);

        return $next($request);
    }
}
