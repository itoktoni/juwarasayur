<?php

use Modules\Catalog\Models\Product;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\Chatbot\Services\ChatbotService;
use Modules\So\Models\So;

it('creates a chatbot conversation session for a WhatsApp notelp', function () {
    /** @var ChatbotService $bot */
    $bot = app(ChatbotService::class);

    $reply = $bot->respond('whatsapp', '628111122223', 'Assalamualaikum');

    expect($reply)->toContain('nama');

    $session = ChatbotSession::where('channel', 'whatsapp')
        ->where('messenger_user', '628111122223')
        ->first();

    expect($session)->not->toBeNull();
    expect($session->state)->toBe('awaiting_name');
});

it('guides a WhatsApp customer through ordering and saves an So order', function (): void {
    Product::create([
        'product_nama' => 'Tomat Merah Segar',
        'product_harga' => 10000,
        'product_status' => 'active',
        'is_active' => true,
    ]);

    /** @var ChatbotService $bot */
    $bot = app(ChatbotService::class);
    $phone = '628111122224';

    $bot->respond('whatsapp', $phone, 'Assalam', $phone); // -> ask name
    $bot->respond('whatsapp', $phone, 'Budi', $phone); // -> set name + menu
    $bot->respond('whatsapp', $phone, 'tomat', $phone); // -> show price, ask qty
    $bot->respond('whatsapp', $phone, '2', $phone); // -> add 2x to cart
    $bot->respond('whatsapp', $phone, 'keranjang', $phone); // -> show cart
    $bot->respond('whatsapp', $phone, 'checkout', $phone); // -> confirm prompt
    $bot->respond('whatsapp', $phone, 'ya', $phone); // -> create order

    $so = So::where('so_customer_phone', $phone)
        ->where('so_status', 'pending')
        ->first();

    expect($so)->not->toBeNull();
    expect($so->has_details()->count())->toBe(1);
    expect($so->has_details()->first()->has_product->field_primary)->toBe(Product::first()->id);
    expect($so->so_payment_token)->not->toBeNull();
});
