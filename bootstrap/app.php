<?php

use App\Http\Middleware\AccessMiddleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\VerifyChatOrigin;
use App\Http\Middleware\VerifyVerified;
use App\Providers\ModelAliasServiceProvider;
use Ibex\CrudGenerator\CrudServiceProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Validation\ValidationException;
use Milon\Barcode\BarcodeServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        ModelAliasServiceProvider::class,
        CrudServiceProvider::class,
        BarcodeServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        TrustProxies::at('*');

        $middleware->alias([
            'access' => AccessMiddleware::class,
            'admin' => AdminMiddleware::class,
            'verified' => VerifyVerified::class,
            // 'skip_verified' => SkipVerifiedCheck::class,
        ]);

        // Endpoint AJAX chat: tanpa session (menghindari lock saat AI berpikir lama),
        // identitas memakai cookie chat_web_token + proteksi origin.
        $middleware->group('chat', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            VerifyChatOrigin::class,
            SubstituteBindings::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'wms/forklift/*',
            'chatbot/webhook/telegram',
            'chatbot/webhook/whatsapp',
            'chat/*',
            'chat/send',
        ]);

        $middleware->append([
            TrustProxies::class,
            HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => false,
                    'code' => 422,
                    'message' => 'The given data was invalid.',
                    'data' => $e->validator->errors()->getMessages(),
                ], 422);
            }

            // Web: keep Laravel's default redirect-back (inline field errors)
            // but also push a toast notification so validation failures are visible.
            if (! $e->validator->errors()->isEmpty()) {
                flash()->error($e->validator->errors()->first());
            }
        });
    })->create();
