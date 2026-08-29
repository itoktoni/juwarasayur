<?php

use App\Models\User;
use Illuminate\Support\Str;
use Modules\So\Models\So;

function notifyHookBody(float $uniqueAmount): string
{
    return json_encode([
        'ip' => '127.0.0.1',
        'payload' => [
            'rule' => 'gopay',
            'package' => 'com.gojek.gopaymerchant',
            'app' => 'GoPay Merchant',
            'title' => 'Pembayaran QRIS statis diterima',
            'text' => 'Rp '.number_format($uniqueAmount, 0, ',', '.').' di Home Pimpah, BERBAH.',
            'timestamp' => now()->toIso8601String(),
            'notificationKey' => '0|com.gojek.gopaymerchant|125|null|10480',
            'username' => 'secret',
        ],
    ]);
}

function notifyHookSignature(string $body, string $secret): string
{
    return hash_hmac('sha256', $body, $secret);
}

function createPendingSo(float $uniqueAmount): So
{
    return So::create([
        'so_code' => 'SO-'.strtoupper(Str::random(6)),
        'so_tanggal' => now()->toDateString(),
        'so_id_reseller' => User::factory()->create()->id,
        'so_shipping_method' => 'pickup',
        'so_status' => 'pending',
        'so_unique_amount' => $uniqueAmount,
    ]);
}

it('settles a pending order from a signed NotifyHook notification', function (): void {
    $_ENV['NOTIFYHOOK_SECRET'] = 'test-secret';
    $_SERVER['NOTIFYHOOK_SECRET'] = 'test-secret';

    $uniqueAmount = 100039.0;
    $so = createPendingSo($uniqueAmount);

    $body = notifyHookBody($uniqueAmount);

    $response = $this->postJson('/api/payment/webhook', json_decode($body, true), [
        'X-NotifyHook-Signature' => notifyHookSignature($body, 'test-secret'),
        'Content-Type' => 'application/json',
    ]);

    $response->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.so_code', $so->so_code)
        ->assertJsonPath('data.amount', 100039)
        ->assertJsonPath('data.method', 'qris');

    expect($so->fresh()->so_status)->toBe('paid');

    unset($_ENV['NOTIFYHOOK_SECRET'], $_SERVER['NOTIFYHOOK_SECRET']);
});

it('accepts the NotifyHook mock envelope even when no amount is present', function (): void {
    $_ENV['NOTIFYHOOK_SECRET'] = 'test-secret';
    $_SERVER['NOTIFYHOOK_SECRET'] = 'test-secret';

    $envelope = [
        'ip' => '127.0.0.1',
        'payload' => [
            'rule' => 'gopay',
            'package' => 'com.gojek.gopaymerchant',
            'app' => 'NotifyHook Test',
            'title' => 'Test notification',
            'text' => 'This is a mock payload from NotifyHook.',
            'timestamp' => '2026-08-29T08:40:51.687+07:00',
            'notificationKey' => 'test|1787967651687',
            'token' => '0215443294',
        ],
        'raw_body' => [
            'rule' => 'gopay',
            'package' => 'com.gojek.gopaymerchant',
            'app' => 'NotifyHook Test',
            'title' => 'Test notification',
            'text' => 'This is a mock payload from NotifyHook.',
            'timestamp' => '2026-08-29T08:40:51.687+07:00',
            'notificationKey' => 'test|1787967651687',
            'token' => '0215443294',
        ],
    ];

    // Signature dihitung atas JSON "payload" bagian dalam (skema alternatif NotifyHook)
    $signature = hash_hmac('sha256', json_encode($envelope['payload']), 'test-secret');

    $response = $this->postJson('/api/payment/webhook', $envelope, [
        'X-NotifyHook-Signature' => $signature,
        'Content-Type' => 'application/json',
    ]);

    // Tidak ada nominal Rp di text → diterima (200) tapi tidak ada order yang lunas
    $response->assertOk()->assertJsonPath('status', true);

    unset($_ENV['NOTIFYHOOK_SECRET'], $_SERVER['NOTIFYHOOK_SECRET']);
});

it('settles a pending order from a NotifyHook envelope signed over the inner payload', function (): void {
    $_ENV['NOTIFYHOOK_SECRET'] = 'test-secret';
    $_SERVER['NOTIFYHOOK_SECRET'] = 'test-secret';

    $uniqueAmount = 100042.0;
    $so = createPendingSo($uniqueAmount);

    $envelope = [
        'ip' => '127.0.0.1',
        'payload' => [
            'rule' => 'gopay',
            'package' => 'com.gojek.gopaymerchant',
            'app' => 'GoPay Merchant',
            'title' => 'Pembayaran QRIS statis diterima',
            'text' => 'Rp '.number_format($uniqueAmount, 0, ',', '.').' di Home Pimpah, BERBAH.',
            'timestamp' => now()->toIso8601String(),
            'notificationKey' => '0|com.gojek.gopaymerchant|125|null|10480',
            'username' => 'secret',
        ],
    ];

    $signature = hash_hmac('sha256', json_encode($envelope['payload']), 'test-secret');

    $response = $this->postJson('/api/payment/webhook', $envelope, [
        'X-NotifyHook-Signature' => $signature,
        'Content-Type' => 'application/json',
    ]);

    $response->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.so_code', $so->so_code)
        ->assertJsonPath('data.amount', 100042);

    expect($so->fresh()->so_status)->toBe('paid');

    unset($_ENV['NOTIFYHOOK_SECRET'], $_SERVER['NOTIFYHOOK_SECRET']);
});

it('rejects a NotifyHook payload with an invalid signature', function (): void {
    $_ENV['NOTIFYHOOK_SECRET'] = 'test-secret';
    $_SERVER['NOTIFYHOOK_SECRET'] = 'test-secret';

    $uniqueAmount = 100077.0;
    $so = createPendingSo($uniqueAmount);

    $body = notifyHookBody($uniqueAmount);

    $response = $this->postJson('/api/payment/webhook', json_decode($body, true), [
        'X-NotifyHook-Signature' => 'invalid-signature',
        'Content-Type' => 'application/json',
    ]);

    $response->assertStatus(401);
    expect($so->fresh()->so_status)->toBe('pending');

    unset($_ENV['NOTIFYHOOK_SECRET'], $_SERVER['NOTIFYHOOK_SECRET']);
});

it('still settles orders from the standard amount format', function (): void {
    $_ENV['NOTIFYHOOK_SECRET'] = '';
    $_SERVER['NOTIFYHOOK_SECRET'] = '';

    $so = createPendingSo(100055.0);

    $response = $this->postJson('/api/payment/webhook', ['amount' => 100055]);

    $response->assertOk()->assertJsonPath('status', true);
    expect($so->fresh()->so_status)->toBe('paid');
});
