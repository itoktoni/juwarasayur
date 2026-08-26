<x-ecommerce::account-layout :title="'Profil'">
    <div class="space-y-5">
        <h2 class="text-2xl font-bold text-on-surface">Profil Saya</h2>

        {{-- Status verifikasi email --}}
        @if(! auth()->user()->hasVerifiedEmail())
            <div class="p-4 rounded-xl border border-warning/40 bg-warning/10">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-warning text-xl mt-0.5">mark_email_unread</span>
                    <div class="flex-1">
                        <p class="font-bold text-sm text-on-surface">Email belum diverifikasi</p>
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            Verifikasi email Anda untuk mengakses semua fitur.
                            @if(session('status') === 'verification-link-sent')
                                <span class="text-primary font-semibold">Link verifikasi baru sudah dikirim ke email Anda.</span>
                            @endif
                        </p>
                        <form method="POST" action="{{ route('verification.send') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline">
                                <span class="material-symbols-outlined text-sm">send</span> Kirim ulang link verifikasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-primary/30 bg-primary/5 w-fit">
                <span class="material-symbols-outlined text-primary text-base">verified</span>
                <span class="text-xs font-semibold text-on-surface">Email terverifikasi</span>
            </div>
        @endif

        {{-- Info profil --}}
        <form method="POST" action="{{ route('account.profile.update') }}"
            class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
            @csrf

            <div class="flex items-center gap-4 mb-5">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-surface-container grid place-items-center shrink-0">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-3xl text-on-surface-variant">person</span>
                    @endif
                </div>
                <div>
                    <p class="font-bold text-on-surface">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-on-surface-variant">{{ auth()->user()->email }}</p>
                </div>
            </div>
            @method('POST')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Nama <span class="text-error">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('name') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('name')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Email <span class="text-error">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('email') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('email')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">No. HP / WhatsApp</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                        placeholder="cth: 081234567890"
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('phone') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('phone')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5 pt-4 border-t border-outline-variant/60">
                <button type="submit" class="btn btn-primary btn-sm gap-1">
                    <span class="material-symbols-outlined text-base">save</span> Simpan Profil
                </button>
            </div>
        </form>

        {{-- Ganti password --}}
        <form method="POST" action="{{ route('account.password.update') }}"
            class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
            @csrf

            <h3 class="font-bold text-on-surface mb-1">Keamanan</h3>
            <p class="text-xs text-on-surface-variant mb-4">Ganti password akun Anda.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Password Saat Ini <span class="text-error">*</span></label>
                    <input type="password" name="current_password" required
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('current_password') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('current_password')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Password Baru <span class="text-error">*</span></label>
                    <input type="password" name="password" required minlength="6"
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('password') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('password')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Konfirmasi Password Baru <span class="text-error">*</span></label>
                    <input type="password" name="password_confirmation" required minlength="6"
                        class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                </div>
            </div>

            <div class="flex justify-end mt-5 pt-4 border-t border-outline-variant/60">
                <button type="submit" class="btn btn-primary btn-sm gap-1">
                    <span class="material-symbols-outlined text-base">lock</span> Ganti Password
                </button>
            </div>
        </form>
    </div>
</x-ecommerce::account-layout>
