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

    public function previewgeneratefromso(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    public function dogeneratefromso(User $user): Response
    {
        return $this->accessProtected($user, __FUNCTION__) ? Response::deny() : Response::allow();
    }

    // Print continues struk PO 80mm (GET /po/po/print-continues)
    public function printcontinues(User $user): Response
    {
        return $this->table($user);
    }
}
