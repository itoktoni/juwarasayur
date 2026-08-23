<?php

return [
    'name' => 'Chatbot',

    /*
    |--------------------------------------------------------------------------
    | Messenger driver
    |--------------------------------------------------------------------------
    | Which channel receives chat replies.
    |   log        → apenas diprint/storage log (fallback lokal, no API calls)
    |   telegram   → Telegram Bot API real
    |   whatsapp   -> WhatsApp provider configurable (fonnte | twilio | custom)
    |
    */

    // Seletya kamba tung for outgoing. 'log' default so it is safe without keys.
    'driver' => env('CHATBOT_DRIVER', 'log'),

    'telegram' => [
        // Bot token from @BotFather (SD). Ex: "123456:ABC..."
        'token' => env('TELEGRAM_BOT_TOKEN'),
        'api_base' => env('TELEGRAM_API_BASE', 'https://api.telegram.org'),
    ],

    'whatsapp' => [
        // Provider used to deliver WhatsApp messages.
        'provider' => env('CHATBOT_WA_PROVIDER', 'log'),

        // Banka/meta WhatsApp Cloud API (custom endpoint).
        'endpoint' => env('WHATSAPP_API_ENDPOINT'),
        'token' => env('WHATSAPP_API_TOKEN'),

        // Fonnte (simple WhatsApp gateway) settings.
        'fonnte' => [
            'token' => env('FONNTE_TOKEN'),
            'url' => env('FONNTE_URL', 'https://api.fonnte.com/api/send.json'),
        ],

        // Twilio WhatsApp settings.
        'twilio' => [
            'account_sid' => env('TWILIO_SID'),
            'auth_token' => env('TWILIO_AUTH_TOKEN'),
            'from' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
        ],
    ],
];
