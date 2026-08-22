<?php

namespace Modules\Ecommerce\Listeners;

use Illuminate\Auth\Events\Login;
use Modules\Ecommerce\Services\CartService;

class MergeSessionCartOnLogin
{
    public function handle(Login $event): void
    {
        CartService::mergeSessionToDb($event->user);
    }
}
