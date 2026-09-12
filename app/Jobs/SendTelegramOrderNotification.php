<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\So\Models\So;

class SendTelegramOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public int $soId)
    {
        $this->afterCommit = true;
    }

    public function handle(TelegramService $telegram): void
    {
        $so = So::with(['has_details.has_product', 'has_customer', 'has_reseller'])->find($this->soId);

        if (! $so) {
            Log::warning('SendTelegramOrderNotification: SO not found', ['id' => $this->soId]);

            return;
        }

        $ok = $telegram->sendOrderNotification($so);

        if (! $ok) {
            Log::info('SendTelegramOrderNotification: skipped/failed — will not retry if config missing', ['so' => $so->so_code]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendTelegramOrderNotification: failed after retries', ['so_id' => $this->soId, 'msg' => $e->getMessage()]);
    }
}
