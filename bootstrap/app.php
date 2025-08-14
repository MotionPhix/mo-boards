<?php

declare(strict_types=1);

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__ . '/../routes/web.php',
    commands: __DIR__ . '/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware): void {
    $middleware->encryptCookies(except: ['appearance']);

    $middleware->web(append: [
      HandleAppearance::class,
      HandleInertiaRequests::class,
      AddLinkHeadersForPreloadedAssets::class,
    ]);

    $middleware->api([
      \Illuminate\Session\Middleware\StartSession::class,
      \Illuminate\View\Middleware\ShareErrorsFromSession::class,
      \Illuminate\Cookie\Middleware\EncryptCookies::class,
      \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
      \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ]);

    $middleware->alias([
      'ensure.company.access' => \App\Http\Middleware\EnsureUserCanAccessCompany::class,
      'plan.feature' => \App\Http\Middleware\EnsurePlanFeature::class,
      'plan.limit' => \App\Http\Middleware\EnsurePlanLimit::class,
      'subscription.feature' => \App\Http\Middleware\CheckSubscriptionFeature::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions): void {
    //
  })->create();
