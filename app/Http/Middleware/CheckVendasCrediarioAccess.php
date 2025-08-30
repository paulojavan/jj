<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckVendasCrediarioAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Verifica se o usuário está autenticado e se tem permissão para vendas no crediário
        if (!$user || !$user->vendas_crediario) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar vendas no crediário.');
        }

        return $next($request);
    }
}