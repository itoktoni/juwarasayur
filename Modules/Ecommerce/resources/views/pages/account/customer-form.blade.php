<?php /** @var App\Models\User|null $customer */ ?>
<x-ecommerce::account-layout :title="isset($customer) && $customer?->exists ? 'Edit Customer' : 'Tambah Customer'">
    <div class="space-y-5">
        <a href="{{ route('account.customers') }}" class="text-sm text-primary hover:underline inline-flex items-center gap-1">
            <span class="material-symbols-outlined text-base">arrow_back</span> Customer Saya
        </a>

        <form method="POST"
            action="{{ isset($customer) && $customer?->exists ? route('account.customers.update', ['id' => $customer->id]) : route('account.customers.store') }}"
            class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest">
            @csrf

            <h3 class="font-bold text-on-surface mb-1">{{ isset($customer) && $customer?->exists ? 'Edit Customer' : 'Customer Baru' }}</h3>
            <p class="text-xs text-on-surface-variant mb-4">Customer terhubung ke akun reseller Anda untuk pemesanan.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Nama <span class="text-error">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $customer?->name) }}" required
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('name') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('name')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Email <span class="text-error">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $customer?->email) }}" required
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('email') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('email')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">No. HP / WhatsApp</label>
                    <input type="tel" name="phone" value="{{ old('phone', $customer?->phone) }}"
                        placeholder="cth: 081234567890"
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('phone') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('phone')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-on-surface mb-1">Password <span class="text-error">*</span></label>
                    <input type="password" name="password" {{ isset($customer) && $customer?->exists ? '' : 'required' }} minlength="6"
                        placeholder="{{ isset($customer) && $customer?->exists ? 'Kosongkan jika tidak ingin mengganti' : 'Min. 6 karakter' }}"
                        class="w-full h-12 px-4 bg-white border {{ $errors->has('password') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    @error('password')<span class="text-xs text-error block mt-1">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-5 pt-4 border-t border-outline-variant/60">
                <a href="{{ route('account.customers') }}" class="btn btn-ghost btn-sm">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm gap-1">
                    <span class="material-symbols-outlined text-base">save</span> Simpan
                </button>
            </div>
        </form>
    </div>
</x-ecommerce::account-layout>
