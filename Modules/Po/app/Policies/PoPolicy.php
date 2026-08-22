<?php

namespace Modules\Po\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\Response;

class PoPolicy extends BasePolicy
{
    public function prepareproduct(User $user): Response
    {
        return $this->prepare($user);
    }
}
