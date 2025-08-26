<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMensagensOciosos
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = auth()->id();
        $cacheKey = "mensagem_ocioso_rate_limit_{$userId}";
        
        // Verificar se o usuário já enviou mensagens recentemente
        $attempts = Cache::get($cacheKey, 0);
        
        // Limite: máximo 10 mensagens por minuto por usuário
        if ($attempts >= 10) {
            return response()->json([
                'success' => false,
                'message' => 'Limite de mensagens excedido. Tente novamente em alguns minutos.'
            ], 429);
        }
        
        // Incrementar contador
        Cache::put($cacheKey, $attempts + 1, 60); // 60 segundos
        
        return $next($request);
    }
}
