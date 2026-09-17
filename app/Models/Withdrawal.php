<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Modules\So\Enums\SoStatusEnum;

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
     * Hanya order yang SUDAH DIBAYAR (paid/confirmed/shipped/delivered) yang
     * dihitung — order pending belum menghasilkan komisi yang bisa dicairkan.
     * Untuk affiliator: sum fee_amount snapshot per so_detail. Tidak ada fallback
     * rumus % x omzet: fallback lama bikin pending ikut terhitung & angka earned
     * tidak cocok dengan rincian fee per order di dashboard.
     */
    public static function earned(User $user): float
    {
        if (! $user->isAffiliator()) {
            return 0;
        }

        return (float) DB::table('so_order_details')
            ->join('so_orders', 'so_orders.id', '=', 'so_order_details.so_detail_id_so')
            ->where('so_orders.so_id_reseller', $user->id)
            ->whereIn('so_orders.so_status', [
                SoStatusEnum::PAID,
                SoStatusEnum::CONFIRMED,
                SoStatusEnum::SHIPPED,
                SoStatusEnum::DELIVERED,
            ])
            ->sum('so_order_details.fee_amount');
    }

    /**
     * Komisi yang sudah dicairkan / sedang diproses.
     * Hanya status 'paid' yang mengurangi saldo. Withdraw 'pending' masih
     * berupa pengajuan (belum keluar uang) jadi tidak mengunci saldo;
     * validasi saldo saat pengajuan baru tetap via cek balance - amount.
     * 'rejected' tidak pernah mengurangi.
     */
    public static function withdrawn(User $reseller): float
    {
        return (float) $reseller->has_withdrawals()
            ->where('status', self::STATUS_PAID)
            ->sum('amount');
    }

    public static function balance(User $reseller): float
    {
        return max(0, static::earned($reseller) - static::withdrawn($reseller));
    }
}
