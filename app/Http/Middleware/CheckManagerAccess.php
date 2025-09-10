<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckManagerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Forbid manager (role 1999) from add/edit user pages
        if ($user && $user->role === '1999') {
            $path = $request->path();
            $isNewUserPage = str_contains($path, 'users/new-user');
            $isUserEditPage = preg_match('/^users\/[^\/]+/', $path);
            
            if ($isNewUserPage || $isUserEditPage) {
                return new Response('Forbidden', 403);
            }
        }
        
        return $next($request);
    }
}
