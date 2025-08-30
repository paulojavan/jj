<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.product.access' => \App\Http\Middleware\CheckProductAccess::class,
            'verificacao.limite' => \App\Http\Middleware\VerificacaoLimiteMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'rate.limit.mensagens' => \App\Http\Middleware\RateLimitMensagensOciosos::class,
            'check.funcionario.access' => \App\Http\Middleware\CheckFuncionarioAccess::class,
            'check.vendas.crediario' => \App\Http\Middleware\CheckVendasCrediarioAccess::class,
            'check.recebimentos' => \App\Http\Middleware\CheckRecebimentosAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
