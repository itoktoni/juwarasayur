<x-layouts::auth :title="__('Daftar Reseller')">
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col text-center">
            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                <x-lucide-store class="size-8 text-primary" />
            </div>
            <h1 class="text-xl font-semibold">{{ __('Daftar Jadi Reseller') }}</h1>
            <p class="text-sm text-base-content/60">{{ __('Bergabung dan mulai kelola orderan pelanggan Anda sendiri') }}</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-form :action="route('register.store')" method="POST">
            <input type="hidden" name="as" value="reseller">

            <div class="bg-base-100 rounded-lg shadow-sm p-4 space-y-4">
                <x-input name="name" type="text" :label="__('Nama Lengkap / Nama Toko')" placeholder="cth: Toko Bu Sari" required />
                <x-input name="phone" type="tel" :label="__('No. WhatsApp')" placeholder="08xxxxxxxxxx" />
                <x-input name="email" type="email" :label="__('Email address')" placeholder="email@example.com" required />
                <x-input name="password" type="password" :label="__('Password')" placeholder="Password" required />
                <x-input name="password_confirmation" type="password" :label="__('Konfirmasi password')" placeholder="Ulangi password" required />

                {{-- CAPTCHA --}}
                <div>
                    <label class="text-sm font-semibold block mb-2">Captcha</label>
                    <div class="flex items-center gap-4">
                        <img src="{{ route('captcha.contact', ['key' => $captchaKey = uniqid()]) }}" alt="Captcha" class="rounded-lg border border-outline-variant" style="height:56px;" id="captcha-image" />
                        <button type="button" onclick="document.getElementById('captcha-image').src='{{ route('captcha.contact') }}?key='+document.querySelector('input[name=captcha_key]').value+'&_='+Date.now()" class="px-4 py-2.5 bg-surface-container border border-outline-variant rounded-xl text-sm hover:bg-surface-container-high transition-colors">
                            Refresh
                        </button>
                    </div>
                    <input type="hidden" name="captcha_key" value="{{ $captchaKey }}">
                    <input type="text" name="captcha" required placeholder="Masukkan hasil captcha"
                        class="w-full px-4 py-3 bg-surface-container-low border border-outline-variant rounded-xl outline-none @error('captcha') border-red-500 @enderror mt-3" />
                    @error('captcha')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                </div>

                <x-button type="submit" class="w-full">{{ __('Daftar Sekarang') }}</x-button>
            </div>
        </x-form>

        <div class="text-center text-sm text-base-content/60 space-y-1">
            <div>
                <span>{{ __('Sudah punya akun?') }}</span>
                <a href="{{ route('login') }}" class="link link-primary">{{ __('Log in') }}</a>
            </div>
            <div>
                <a href="{{ route('register') }}" class="link link-secondary">{{ __('Daftar sebagai user biasa') }}</a>
            </div>
        </div>
    </div>
</x-layouts::auth>
