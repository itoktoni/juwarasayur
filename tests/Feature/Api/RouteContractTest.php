<?php

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

it('exposes exactly the expected API route surface', function () {
    $apiUris = collect(RouteFacade::getRoutes())
        ->map(fn (Route $route) => $route->uri())
        ->filter(fn (string $uri) => str_starts_with($uri, 'api/'))
        ->unique()
        ->sort()
        ->values()
        ->all();

    expect($apiUris)->toBe([
        'api/content/{slug?}',
        'api/login',
        'api/logout',
        'api/me',
        'api/users',
        'api/users/boot',
        'api/users/create',
        'api/users/delete',
        'api/users/delete/{id}',
        'api/users/show/{id}',
        'api/users/table',
        'api/users/trait-post-create',
        'api/users/trait-post-update/{id}',
        'api/users/update/{id}',
    ]);
});
