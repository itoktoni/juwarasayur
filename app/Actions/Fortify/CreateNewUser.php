<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserTypeEnum;
use App\Http\Middleware\CaptureAffiliateRef;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'phone' => ['nullable', 'string', 'max:20'],
            // Form register reseller mengirim as=reseller agar akun otomatis ber-type reseller
            'as' => ['nullable', 'string', 'in:'.UserTypeEnum::RESELLER],
            'password' => $this->passwordRules(),
        ])->validate();

        // Attribution: affiliator/reseller link ?ref=CODE atau /r/CODE → cookie/session aff_ref
        $referenceId = null;
        try {
            $referenceId = CaptureAffiliateRef::resolvedAffiliatorId(request());
        } catch (\Throwable $e) {
        }
        // Cegah self-referral tidak mungkin di register (belum punya id), tapi tetap jaga jika code milik email yang sama
        // reference_id hanya diisi saat register; tidak overwrite jika sudah ada

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'phone' => $input['phone'] ?? null,
            'type' => ($input['as'] ?? null) === UserTypeEnum::RESELLER ? UserTypeEnum::RESELLER : 'user',
            // Fee komisi awal dari konfigurasi global; hanya admin yang bisa adjust per-reseller
            'fee' => ($input['as'] ?? null) === UserTypeEnum::RESELLER ? (float) config('commission.rate', 2) : null,
            'reference_id' => $referenceId,
        ]);
    }
}
