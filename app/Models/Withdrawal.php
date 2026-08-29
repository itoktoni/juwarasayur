<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;

#[Fillable(['user_id', 'amount', 'bank_name', 'bank_account_name', 'bank_account_no', 'status', 'note', 'processed_at'])]
class Withdrawal extends BaseModel
{
    protected $table = 'withdrawals';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_REJECTED = 'rejected';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public static function field_name(): string
    {
        return 'id';
    }

    public static $filterColumns = [
        'status' => 'Status',
        'bank_name' => 'Bank',
    ];

    public static $sortColumns = ['created_at', 'amount', 'status'];

    public function has_user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Komisi yang dihasilkan affiliator (belum dikurangi withdraw).
     * Reseller tidak dapat komisi (hanya diskon harga), return 0.
     * Untuk affiliator: sum fee_amount snapshot per so_detail, fallback ke rumus lama untuk data sebelum migration.
     */
    public static function earned(User $user): float
    {
        if (! $user->isAffiliator()) {
            return 0;
        }

        $sum = (float) DB::table('so_order_details')
            ->join('so_orders', 'so_orders.id', '=', 'so_order_details.so_detail_id_so')
            ->where('so_orders.so_id_reseller', $user->id)
            ->whereNot('so_orders.so_status', SoStatusEnum::CANCELLED)
            ->sum('so_order_details.fee_amount');

        if ($sum > 0) {
            return $sum;
        }

        // Fallback untuk data lama sebelum snapshot fee_amount
        return (float) So::query()
            ->where('so_id_reseller', $user->id)
            ->whereNot('so_status', SoStatusEnum::CANCELLED)
            ->sum('so_grand_total') * $user->effectiveFee() / 100;
    }

    /**
     * Komisi yang sudah dicairkan / sedang diproses.
     */
    public static function withdrawn(User $reseller): float
    {
        return (float) $reseller->has_withdrawals()
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PAID])
            ->sum('amount');
    }

    public static function balance(User $reseller): float
    {
        return max(0, static::earned($reseller) - static::withdrawn($reseller));
    }
}
