<?php

namespace Raison\FilamentStarter\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DeveloperGateMiddleware
 *
 * Secures selected routes using a static password for developers.
 */
class DeveloperGateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('filament-starter.developer_gate.enabled', false)) {
            return $next($request);
        }

        if ($request->session()->get('starter_developer_gate_passed')) {
            return $next($request);
        }

        if ($request->is('starter/developer-gate')) {
            return $next($request);
        }

        return redirect()->guest(route('starter.developer-gate.login'));
    }
}
