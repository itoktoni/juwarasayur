<?php

namespace Modules\So\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\Catalog\Models\Product;
use Modules\So\Enums\ConsignmentStatusEnum;

#[Fillable(['code', 'user_id', 'consignment_date', 'note', 'status', 'total_qty', 'total_sold', 'total_returned', 'total_amount', 'settled_at'])]
class Consignment extends BaseModel
{
    protected $table = 'consignments';

    public static $sortColumns = ['code', 'consignment_date', 'status', 'total_amount'];

    public static $filterColumns = [
        'code' => 'Kode',
        'status' => 'Status',
    ];

    protected function casts(): array
    {
        return [
            'consignment_date' => 'date',
            'total_qty' => 'decimal:2',
            'total_sold' => 'decimal:2',
            'total_returned' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'settled_at' => 'datetime',
        ];
    }

    public static function field_name(): string
    {
        return 'code';
    }

    public function has_reseller(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function has_details(): HasMany
    {
        return $this->hasMany(ConsignmentDetail::class, 'consignment_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->code)) {
                $model->code = static::generateCode();
            }
            if (empty($model->status)) {
                $model->status = ConsignmentStatusEnum::OPEN;
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'TJ-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Hitung ulang total titipan dari detail.
     */
    public function recalculateTotals(): void
    {
        $details = $this->has_details()->get();

        $this->updateQuietly([
            'total_qty' => $details->sum('qty'),
            'total_sold' => $details->sum(fn ($d) => (float) ($d->qty_sold ?? 0)),
            'total_returned' => $details->sum(fn ($d) => (float) ($d->qty_returned ?? 0)),
            'total_amount' => $details->sum(fn ($d) => (int) ($d->qty_sold ?? 0) * (float) $d->price),
        ]);
    }

    /**
     * Setujui penarikan: hitung invoice & tandai settled.
     */
    public function settle(array $rows): void
    {
        foreach ($this->has_details()->get() as $detail) {
            $row = $rows[$detail->id] ?? null;
            if (! is_array($row)) {
                continue;
            }

            $sold = max(0, (float) ($row['qty_sold'] ?? 0));
            $returned = max(0, (float) ($row['qty_returned'] ?? 0));

            if ($sold + $returned > (float) $detail->qty) {
                abort(422, "Total terjual+kembali melebihi jumlah titipan untuk produk {$detail->has_product?->product_nama}.");
            }

            $detail->update([
                'qty_sold' => $sold,
                'qty_returned' => $returned,
            ]);
        }

        $this->recalculateTotals();
        $this->updateQuietly([
            'status' => ConsignmentStatusEnum::SETTLED,
            'settled_at' => now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'consignment_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:catalog_products,id'],
            'details.*.qty' => ['required', 'numeric', 'min:1'],
            'details.*.price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
