<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckProductAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Verifica se o usuário está autenticado e se é administrador ou tem permissão para cadastro de produtos
        if ($user && ($user->nivel === 'administrador' || $user->cadastro_produtos === true)) {
            return $next($request);
        }

        // Se não tiver permissão, redireciona ou retorna um erro
        // Pode redirecionar para uma página de 'não autorizado' ou para o dashboard com uma mensagem de erro
        return redirect()->route('dashboard')->with('error', 'Você não tem permissão para acessar esta área.');
    }
}
