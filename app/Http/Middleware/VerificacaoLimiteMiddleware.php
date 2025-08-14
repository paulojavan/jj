<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificacaoLimiteMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para acessar esta página.');
        }

        $user = Auth::user();

        // Verificar se o usuário é administrador (nivel = 'admin') ou tem permissão de limite
        $isAdmin = $user->nivel === 'admin';
        $hasLimitePermission = $user->limite === true || $user->limite === 1 || $user->limite === '1';

        if (!$isAdmin && !$hasLimitePermission) {
            return redirect()->back()->with('error', 'Você não tem permissão para acessar a verificação de limite.');
        }

        return $next($request);
    }
}