<?php

namespace App\Http\Middleware;

use App\Models\CompanyAccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateUrlTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $providedToken = (string) $request->query('token', '');

        if ($providedToken === '') {
            abort(Response::HTTP_FORBIDDEN, 'Invalid or missing token.');
        }

        $adminToken = (string) config('app.admin_access_token', '');
        $viewAllToken = (string) config('app.url_access_token', '');

        if ($adminToken !== '' && hash_equals($adminToken, $providedToken)) {
            $request->attributes->set('access_scope', 'admin');

            return $next($request);
        }

        if ($viewAllToken !== '' && hash_equals($viewAllToken, $providedToken)) {
            $request->attributes->set('access_scope', 'view_all');

            return $next($request);
        }

        $companyToken = CompanyAccessToken::query()->where('token', $providedToken)->first();

        if ($companyToken !== null) {
            $request->attributes->set('access_scope', 'company');
            $request->attributes->set('access_company_name', $companyToken->company_name);

            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, 'Invalid or missing token.');
    }
}
