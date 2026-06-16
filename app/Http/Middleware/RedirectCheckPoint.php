<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectCheckPoint
{
    public function handle(Request $request, Closure $next)
    {
        
        if (!auth()->check()) {
            return redirect('/admin/login');
        }

    
        if (!auth()->user()->hasRole(config('roles.super_admin'))) {
            return redirect('/portal');
        }

        return $next($request);
    }
}