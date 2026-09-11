<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    public function sendMessage(string $text, ?string $chatId = null, ?int $threadId = null, string $parseMode = 'HTML'): bool
    {
        $token = trim((string) config('services.telegram.bot_token', env('TELEGRAM_BOT_TOKEN', '')));
        $groupId = trim((string) ($chatId ?? config('services.telegram.group_id', env('TELEGRAM_GROUP_ID', ''))));

        if ($token === '' || $groupId === '') {
            Log::info('TelegramService: skipped — token/group empty', ['has_token' => $token !== '', 'group' => $groupId]);

            return false;
        }

        $url = 'https://api.telegram.org/bot'.$token.'/sendMessage';

        $payload = [
            'chat_id' => $groupId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => true,
        ];

        if ($threadId !== null && $threadId > 0) {
            $payload['message_thread_id'] = $threadId;
        }

        try {
            $res = Http::timeout(10)->post($url, $payload);

            if ($res->successful()) {
                return true;
            }

            Log::warning('TelegramService: send failed', ['status' => $res->status(), 'body' => $res->body(), 'payload' => $payload]);

            return false;
        } catch (\Throwable $e) {
            Log::error('TelegramService: exception', ['msg' => $e->getMessage()]);

            return false;
        }
    }

    public function sendOrderNotification(\Modules\So\Models\So $so): bool
    {
        $so->loadMissing(['has_details.has_product', 'has_customer', 'has_reseller']);

        $customer = $so->so_customer_name ?: ($so->has_customer?->name ?? 'Tamu');
        $phone = $so->so_customer_phone ?: ($so->has_customer?->phone ?? '-');
        $reseller = $so->has_reseller?->name ? ' (via '.$so->has_reseller->name.')' : '';
        $method = \Modules\So\Enums\ShippingMethodEnum::getDescription($so->so_shipping_method);
        if ($so->so_cod_location) {
            $method .= ' — '.$so->so_cod_location;
        }

        $lines = $so->has_details->map(function ($d) {
            $nama = $d->has_product?->product_nama ?? '-';
            $qty = (int) $d->so_detail_qty;
            $harga = (float) $d->so_detail_harga;
            $sub = $qty * $harga;

            return '• '.e($nama).' ×'.$qty.' @ Rp '.number_format($harga, 0, ',', '.').' = Rp '.number_format($sub, 0, ',', '.');
        })->implode("\n");

        $grand = number_format((float) $so->so_grand_total, 0, ',', '.');
        $unique = number_format((float) ($so->so_unique_amount ?? $so->so_grand_total), 0, ',', '.');
        $link = url('/payment/'.$so->so_payment_token);
        $adminLink = url('admin/so/so/table');

        $text = "🛒 <b>Order Baru</b> #{$so->so_code}{$reseller}\n"
            ."👤 <b>Customer:</b> ".e($customer)." — ".e($phone)."\n"
            ."📦 <b>Metode:</b> ".e($method)."\n"
            ."📅 <b>Tanggal:</b> ".e(optional($so->so_tanggal)->format('d/m/Y') ?? now()->format('d/m/Y'))."\n"
            ."💰 <b>Total:</b> Rp {$grand} (unik Rp {$unique})\n"
            ."📊 <b>Status:</b> ".e($so->so_status)."\n\n"
            ."<b>Rincian:</b>\n".$lines."\n\n"
            ."🔗 <b>Bayar:</b> {$link}\n"
            ."🛠 <a href=\"{$adminLink}\">Buka Admin SO</a>";

        // potong jika terlalu panjang (>4000 char telegram limit)
        if (mb_strlen($text) > 3800) {
            $text = mb_substr($text, 0, 3800)."\n… (dipotong)";
        }

        // thread per platform jika ada — fallback ke group utama tanpa thread
        $thread = null;
        // contoh: jika mau kirim ke thread telegram, pakai env TELEGRAM_THREAD_TELEGRAM
        // $thread = (int) env('TELEGRAM_THREAD_TELEGRAM');

        return $this->sendMessage($text, null, $thread, 'HTML');
    }
}
