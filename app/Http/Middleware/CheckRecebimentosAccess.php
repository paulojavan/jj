<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRecebimentosAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Verifica se o usuário está autenticado e se tem permissão para recebimentos
        if (!$user || !$user->recebimentos) {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar recebimentos de parcelas.');
        }

        return $next($request);
    }
}