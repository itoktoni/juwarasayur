<?php

namespace Modules\So\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\Response;

class ConsignmentPolicy extends BasePolicy
{
    // Dashboard konsinyasi hari ini (GET /so/consignment/today)
    public function today(User $user): Response
    {
        return $this->table($user);
    }

    // Tarik uang / settle (GET & POST /so/consignment/settle/{id})
    public function settle(User $user): Response
    {
        return $this->table($user);
    }
}
