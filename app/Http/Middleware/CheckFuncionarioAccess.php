<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckFuncionarioAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Verifica se o usuário está autenticado e se é administrador
        if (!$user || $user->nivel !== 'administrador') {
            return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar o gerenciamento de funcionários. Apenas administradores podem acessar esta área.');
        }

        return $next($request);
    }
}