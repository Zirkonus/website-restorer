<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class IsLicenceSuspend
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(Auth::user()->licence == 'suspend'){
            Auth::logout();
            return redirect('/licence/suspend');
        }
        return $next($request);
    }
}
