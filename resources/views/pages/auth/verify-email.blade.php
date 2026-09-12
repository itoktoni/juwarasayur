<x-layouts::auth :title="__('Email verification')">
    <div class="flex flex-col gap-6">
        @php
            $authUser = auth()->user();
            $isAffiliator = $authUser && in_array($authUser->type, [\App\Enums\UserTypeEnum::AFFILIATOR, \App\Enums\UserTypeEnum::RESELLER], true);
            $emailVerified = $authUser && $authUser->hasVerifiedEmail();
            $adminApproved = $authUser && !empty($authUser->verified_at);
            $needsAdminApproval = $isAffiliator && $emailVerified && !$adminApproved;
        @endphp

        @if($needsAdminApproval)
            <div class="flex w-full flex-col text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-warning/10 text-warning">
                    <span class="material-symbols-outlined text-3xl">hourglass_empty</span>
                </div>
                <h1 class="text-xl font-semibold">{{ __('Menunggu Persetujuan Admin') }}</h1>
                <p class="text-sm text-base-content/60">{{ __('Email kamu sudah terverifikasi. Akun :type kamu sedang ditinjau admin. Kamu akan mendapat akses setelah di-approve.', ['type' => $authUser->type]) }}</p>
            </div>
            <div class="bg-base-100 rounded-lg shadow-sm p-4 space-y-4">
                <div class="alert alert-warning">
                    <span class="icon-[tabler--clock] size-5"></span>
                    <span>{{ __('Tim admin akan memverifikasi data kamu. Hubungi admin jika butuh percepatan.') }}</span>
                </div>
                <x-form :action="route('logout')" method="POST">
                    <x-button type="submit" variant="ghost" class="w-full">{{ __('Log out') }}</x-button>
                </x-form>
            </div>
        @else
            <div class="flex w-full flex-col text-center">
                <h1 class="text-xl font-semibold">{{ __('Verify your email') }}</h1>
                <p class="text-sm text-base-content/60">{{ __('Please verify your email address by clicking on the link we just emailed to you.') }}</p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success">
                    <span class="icon-[tabler--check] size-5"></span>
                    <span>{{ __('A new verification link has been sent to the email address you provided during registration.') }}</span>
                </div>
            @endif

            <div class="bg-base-100 rounded-lg shadow-sm p-4 space-y-4">
                <x-form :action="route('verification.send')" method="POST">
                    <x-button type="submit" class="w-full">{{ __('Resend verification email') }}</x-button>
                </x-form>

                @if($isAffiliator && $emailVerified)
                    <div class="text-xs text-center text-base-content/50">{{ __('Setelah verifikasi email, akun affiliator/reseller tetap butuh approval admin (cek notifikasi).') }}</div>
                @endif

                <x-form :action="route('logout')" method="POST">
                    <x-button type="submit" variant="ghost" class="w-full">{{ __('Log out') }}</x-button>
                </x-form>
            </div>
        @endif
    </div>
</x-layouts::auth>
