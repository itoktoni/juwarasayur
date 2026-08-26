<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'amount' => 'decimal:2',
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
     * Komisi yang dihasilkan reseller (belum dikurangi withdraw).
     * Rate pakai fee milik reseller; jika NULL fallback ke config global.
     */
    public static function earned(User $reseller): float
    {
        return (float) \Modules\So\Models\So::query()
            ->where('so_id_reseller', $reseller->id)
            ->whereNot('so_status', \Modules\So\Enums\SoStatusEnum::CANCELLED)
            ->sum('so_grand_total') * $reseller->effectiveFee() / 100;
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
