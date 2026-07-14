<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
//use Illuminate\Support\Facades\Log;

class CheckTokenAndAddToHeaderMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $all = $request->all();
        if (isset($all['_token'])) {
            //Log::debug('token from http param', [$all['_token']]);
            $request->headers->set('Authorization', sprintf('%s %s', 'Bearer', $all['_token']));
        }
        return $next($request);
    }
}
