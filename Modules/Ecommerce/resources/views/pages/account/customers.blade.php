<?php /** @var \Illuminate\Pagination\LengthAwarePaginator $data */ ?>
<x-ecommerce::account-layout :title="'Customer'">
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <h2 class="text-2xl font-bold text-on-surface">Customer Saya</h2>
            <a href="{{ route('account.customers.create') }}" class="btn btn-primary btn-sm gap-1">
                <span class="material-symbols-outlined text-base">person_add</span> Tambah
            </a>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('account.customers') }}" class="flex gap-2">
            <div class="relative flex-1">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-xl pointer-events-none">search</span>
                <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama, email, atau No. HP..."
                    class="w-full h-11 pl-10 pr-4 bg-white border {{ $errors->has('q') ? 'border-error' : 'border-outline-variant' }} rounded-lg text-sm outline-none focus:border-primary focus:ring-1 focus:ring-primary">
            </div>
            @if($q !== '')
                <a href="{{ route('account.customers') }}" class="btn btn-soft h-11" title="Reset pencarian">
                    <span class="material-symbols-outlined text-base">close</span>
                </a>
            @endif
            <button type="submit" class="btn btn-primary h-11 px-4">Cari</button>
        </form>

        @if($data->isEmpty())
            <div class="p-8 rounded-xl border border-dashed border-outline-variant text-center">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40">group</span>
                <p class="mt-3 text-on-surface-variant">Belum ada customer. Tambahkan customer pertama Anda.</p>
            </div>
        @else
            {{-- Desktop: tabel --}}
            <div class="hidden md:block rounded-xl border border-outline-variant bg-surface-container-lowest overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-container text-on-surface-variant text-xs uppercase tracking-wide">
                            <th class="text-left font-semibold px-4 py-3">Customer</th>
                            <th class="text-left font-semibold px-4 py-3">Email</th>
                            <th class="text-left font-semibold px-4 py-3">No. HP</th>
                            <th class="text-center font-semibold px-4 py-3">Total Order</th>
                            <th class="text-right font-semibold px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/60">
                        @foreach($data as $customer)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-full overflow-hidden bg-surface-container grid place-items-center shrink-0">
                                            @if($customer->avatar_url)
                                                <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="material-symbols-outlined text-lg text-on-surface-variant">person</span>
                                            @endif
                                        </div>
                                        <span class="font-semibold text-on-surface truncate">{{ $customer->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-on-surface-variant truncate max-w-[200px]">{{ $customer->email }}</td>
                                <td class="px-4 py-3 text-on-surface-variant whitespace-nowrap">{{ $customer->phone ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-mono">{{ \Modules\So\Models\So::where('so_id_customer', $customer->id)->count() }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form method="POST" action="{{ route('account.customers.delete', ['id' => $customer->id]) }}"
                                            onsubmit="return confirm('Hapus customer {{ $customer->name }}?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors" title="Hapus">
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </form>
                                        <a href="{{ route('account.customers.edit', ['id' => $customer->id]) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors" title="Edit">
                                            <span class="material-symbols-outlined text-lg">edit</span>
                                        </a>
                                        <a href="{{ route('cart.index', ['customer_id' => $customer->id]) }}"
                                            class="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg bg-primary text-on-primary text-xs font-medium hover:opacity-90 transition-opacity" title="Buat order untuk customer ini">
                                            <span class="material-symbols-outlined text-base">add_shopping_cart</span> Order
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile: kartu --}}
            <div class="md:hidden grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($data as $customer)
                    <div class="p-4 rounded-xl border border-outline-variant bg-surface-container-lowest flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden bg-surface-container grid place-items-center shrink-0">
                                @if($customer->avatar_url)
                                    <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="material-symbols-outlined text-xl text-on-surface-variant">person</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-on-surface truncate">{{ $customer->name }}</p>
                                <p class="text-xs text-on-surface-variant truncate">{{ $customer->email }}</p>
                            </div>
                        </div>
                        <dl class="text-xs space-y-1 mb-3">
                            <div class="flex justify-between"><dt class="text-on-surface-variant">No. HP</dt><dd class="text-on-surface font-medium">{{ $customer->phone ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-on-surface-variant">Total Order</dt><dd class="text-on-surface font-mono font-medium">{{ \Modules\So\Models\So::where('so_id_customer', $customer->id)->count() }}</dd></div>
                        </dl>
                        <div class="mt-auto flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <form method="POST" action="{{ route('account.customers.delete', ['id' => $customer->id]) }}"
                                onsubmit="return confirm('Hapus customer {{ $customer->name }}?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors" title="Hapus">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                            <div class="flex gap-1.5">
                                <a href="{{ route('account.customers.edit', ['id' => $customer->id]) }}"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors" title="Edit">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <a href="{{ route('cart.index', ['customer_id' => $customer->id]) }}"
                                    class="inline-flex items-center gap-1 h-8 px-2.5 rounded-lg bg-primary text-on-primary text-xs font-medium hover:opacity-90 transition-opacity" title="Buat order untuk customer ini">
                                    <span class="material-symbols-outlined text-base">add_shopping_cart</span> Order
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <x-pagination :paginator="$data" />
        @endif
    </div>
</x-ecommerce::account-layout>
