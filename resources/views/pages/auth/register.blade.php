<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col text-center">
            <h1 class="text-xl font-semibold">{{ __('Create an account') }}</h1>
            <p class="text-sm text-base-content/60">{{ __('Enter your details below to create your account') }}</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-form :action="route('register.store')" method="POST">
            <div class="bg-base-100 rounded-lg shadow-sm p-4 space-y-4">
                <x-input name="name" type="text" :label="__('Name')" placeholder="Full name" />
                <x-input name="email" type="email" :label="__('Email address')" placeholder="email@example.com" />
                <x-input name="password" type="password" :label="__('Password')" placeholder="Password" />
                <x-input name="password_confirmation" type="password" :label="__('Confirm password')" placeholder="Confirm password" />

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

                <x-button type="submit" class="w-full">{{ __('Create account') }}</x-button>
            </div>
        </x-form>

        <div class="text-center text-sm text-base-content/60 space-y-1">
            <div>
                <span>{{ __('Already have an account?') }}</span>
                <a href="{{ route('login') }}" class="link link-primary">{{ __('Log in') }}</a>
            </div>
            @if (Route::has('register.affiliator'))
                <div>
                    <a href="{{ route('register.affiliator') }}" class="link link-secondary font-semibold">Daftar jadi Affiliator</a>
                </div>
            @endif
        </div>
    </div>
</x-layouts::auth>
