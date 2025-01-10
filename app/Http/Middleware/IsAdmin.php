<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class IsAdmin
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
        if (Auth::user() &&  Auth::user()->is_admin == 1) {
            return $next($request);
        }

     // Si no és administrador, retorna un error d'accés no permès
     return response()->json([
        'status' => false,
        'message' => 'Accés no permès, només els administradors poden realitzar aquesta acció.'
    ], 403);
    }
}
