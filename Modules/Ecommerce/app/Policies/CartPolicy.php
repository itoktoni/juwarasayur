<?php

namespace Modules\Ecommerce\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class CartPolicy
{
    public function before(User $user, string $ability): Response|null|bool
    {
        // Pemilik cart dicek per-record di controller; policy ini longgar.
        return true;
    }
}
