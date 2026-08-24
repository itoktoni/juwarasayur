<?php /** @var Modules\Chatbot\Models\ChatbotSession $model */ ?>

@php
    $messages = \Modules\Chatbot\Models\ChatbotMessage::query()
        ->where('chatbot_session_id', $model->id)
        ->orderBy('id')
        ->limit(300)
        ->get();
@endphp

<x-layouts::app>
    <x-breadcrumb :items="[
        ['url' => moduleRoute('getTable'), 'label' => 'Chatbot Session'],
        ['url' => '', 'label' => 'Riwayat: '.($model->contact_name ?? $model->messenger_user)],
    ]" />

    <div class="content mt-4 lg:mt-0 max-w-4xl">

        {{-- Info sesi --}}
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-4 mb-4">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <span class="badge {{ in_array($model->channel, ['whatsapp','web']) ? 'badge-success' : 'badge-primary' }}">{{ ucfirst($model->channel) }}</span>
                <span><strong>{{ $model->contact_name ?? '-' }}</strong></span>
                <span class="text-on-surface-variant">{{ $model->contact_phone ?? \Illuminate\Support\Str::limit($model->messenger_user, 28) }}</span>
                <span class="text-on-surface-variant">State: <code>{{ $model->state ?? '-' }}</code></span>
                <span class="text-on-surface-variant">Keranjang: {{ is_array($model->cart) ? count($model->cart) : 0 }} item</span>
                <span class="text-on-surface-variant ml-auto">Aktif: {{ $model->last_active_at?->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- Riwayat percakapan --}}
        <div class="rounded-xl border border-outline-variant bg-surface-container-lowest p-5 space-y-3 min-h-[200px]">
            @forelse($messages as $m)
                <div class="flex flex-col {{ $m->role === 'user' ? 'items-start' : 'items-end' }}">
                    <div class="max-w-[75%] rounded-xl px-4 py-2.5 text-sm whitespace-pre-wrap break-words
                        {{ $m->role === 'user'
                            ? 'bg-surface-container-high text-on-surface rounded-tl-sm'
                            : 'bg-primary text-on-primary rounded-tr-sm font-medium' }}">
                        {{ $m->content }}
                    </div>
                    <span class="text-[10px] text-on-surface-variant mt-1 px-1">
                        {{ $m->role === 'user' ? 'Customer' : 'Bot' }} · {{ $m->created_at?->format('d M H:i') }}
                    </span>
                </div>
            @empty
                <p class="text-center text-on-surface-variant py-10">Belum ada riwayat percakapan tersimpan untuk sesi ini.</p>
            @endforelse
        </div>

    </div>
</x-layouts::app>
