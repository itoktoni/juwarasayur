<?php

namespace Modules\So\Policies;

use App\Models\User;
use App\Policies\BasePolicy;
use Illuminate\Auth\Access\Response;

class SoPolicy extends BasePolicy
{
    // Endpoint AJAX hitung ongkir (GET /so/so/shipping-cost)
    public function shippingcost(User $user): Response
    {
        return $this->table($user);
    }

    // Endpoint AJAX ongkir COD (GET /so/so/cod-fee)
    public function codfee(User $user): Response
    {
        return $this->table($user);
    }

    // Print continues struk 80mm (GET /so/so/print-continues)
    public function printcontinues(User $user): Response
    {
        return $this->table($user);
    }

    // Payment QR + link untuk customer (GET /so/so/payment/{id})
    public function payment(User $user): Response
    {
        return $this->table($user);
    }

    // PDF 58mm Bluetooth (GET /so/so/payment-pdf/{id})
    public function paymentpdf(User $user): Response
    {
        return $this->table($user);
    }

    // Regenerate QR (POST /so/so/regenerate-payment/{id})
    public function regeneratepayment(User $user): Response
    {
        return $this->table($user);
    }
}
