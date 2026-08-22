<?php

namespace Modules\Po\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Po\Enums\PoStatusEnum;

#[Fillable(['po_code', 'po_tanggal', 'po_id_supplier', 'po_status', 'po_keterangan', 'po_subtotal', 'po_discount', 'po_discount_type', 'po_discount_note', 'po_dpp', 'po_ppn', 'po_ppn_rate', 'po_pph', 'po_pph_rate', 'po_grand_total'])]
class Po extends BaseModel
{
    use SoftDeletes;

    protected $table = 'po_pos';

    public static $sortColumns = ['po_code', 'po_tanggal', 'po_status', 'po_grand_total'];

    public static $filterColumns = ['po_code', 'po_tanggal', 'po_status', 'po_discount', 'po_discount_note'];

    public static function field_name(): string
    {
        return 'po_code';
    }

    protected function casts(): array
    {
        return [
            'po_tanggal' => 'date',
            'po_subtotal' => 'decimal:2',
            'po_discount' => 'decimal:2',
            'po_dpp' => 'decimal:2',
            'po_ppn' => 'decimal:2',
            'po_ppn_rate' => 'decimal:2',
            'po_pph' => 'decimal:2',
            'po_pph_rate' => 'decimal:2',
            'po_grand_total' => 'decimal:2',
        ];
    }

    protected $attributes = [
        'po_status' => PoStatusEnum::PENDING,
        'po_discount' => 0,
        'po_discount_type' => 'nominal',
        'po_ppn' => 0,
        'po_pph' => 0,
        'po_dpp' => 0,
    ];

    public function has_supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'po_id_supplier');
    }

    public function has_details(): HasMany
    {
        return $this->hasMany(PoDetail::class, 'po_detail_id_po');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->po_code)) {
                $model->po_code = static::generateCode();
            }
            if ($model->po_ppn_rate === null) {
                $model->po_ppn_rate = (float) config('po.ppn_rate', 11);
            }
            if ($model->po_pph_rate === null) {
                $model->po_pph_rate = (float) config('po.pph_rate', 2);
            }
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'PO-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (static::withTrashed()->where('po_code', $code)->exists());

        return $code;
    }

    public static function calculateTotals(float $subtotal, float $discount, string $discountType, float $ppnRate, float $pphRate): array
    {
        $discountAmount = $discountType === 'percent'
            ? min($subtotal * $discount / 100, $subtotal)
            : min($discount, $subtotal);

        $dpp = max(0, $subtotal - $discountAmount);
        $ppn = $dpp * $ppnRate / 100;
        $pph = $dpp * $pphRate / 100;
        $grandTotal = $dpp + $ppn + $pph;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'discount_amount' => $discountAmount,
            'discount_type' => $discountType,
            'dpp' => $dpp,
            'ppn' => $ppn,
            'ppn_rate' => $ppnRate,
            'pph' => $pph,
            'pph_rate' => $pphRate,
            'grand_total' => $grandTotal,
        ];
    }

    public function getPoDiscountAmountAttribute(): float
    {
        return (float) static::calculateTotals(
            (float) $this->po_subtotal,
            (float) $this->po_discount,
            (string) ($this->po_discount_type ?? 'nominal'),
            (float) $this->po_ppn_rate,
            (float) $this->po_pph_rate,
        )['discount_amount'];
    }

    public function getPoDppComputedAttribute(): float
    {
        return (float) static::calculateTotals(
            (float) $this->po_subtotal,
            (float) $this->po_discount,
            (string) ($this->po_discount_type ?? 'nominal'),
            (float) $this->po_ppn_rate,
            (float) $this->po_pph_rate,
        )['dpp'];
    }

    public function getPoPpnAmountAttribute(): float
    {
        return (float) static::calculateTotals(
            (float) $this->po_subtotal,
            (float) $this->po_discount,
            (string) ($this->po_discount_type ?? 'nominal'),
            (float) $this->po_ppn_rate,
            (float) $this->po_pph_rate,
        )['ppn'];
    }

    public function getPoPphAmountAttribute(): float
    {
        return (float) static::calculateTotals(
            (float) $this->po_subtotal,
            (float) $this->po_discount,
            (string) ($this->po_discount_type ?? 'nominal'),
            (float) $this->po_ppn_rate,
            (float) $this->po_pph_rate,
        )['pph'];
    }

    public function getPoGrandTotalComputedAttribute(): float
    {
        return (float) static::calculateTotals(
            (float) $this->po_subtotal,
            (float) $this->po_discount,
            (string) ($this->po_discount_type ?? 'nominal'),
            (float) $this->po_ppn_rate,
            (float) $this->po_pph_rate,
        )['grand_total'];
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->has_details()->get()->sum(fn ($d) => (int) $d->po_detail_qty * (float) $d->po_detail_harga);
        $totals = static::calculateTotals(
            $subtotal,
            (float) ($this->po_discount ?? 0),
            (string) ($this->po_discount_type ?? 'nominal'),
            (float) ($this->po_ppn_rate ?? config('po.ppn_rate', 11)),
            (float) ($this->po_pph_rate ?? config('po.pph_rate', 2)),
        );

        $this->updateQuietly([
            'po_subtotal' => $totals['subtotal'],
            'po_dpp' => $totals['dpp'],
            'po_ppn' => $totals['ppn'],
            'po_pph' => $totals['pph'],
            'po_grand_total' => $totals['grand_total'],
        ]);
    }

    public function rules(): array
    {
        return [
            'po_code' => ['nullable', 'string', 'max:50'],
            'po_tanggal' => ['required', 'date'],
            'po_id_supplier' => ['required', 'exists:po_suppliers,id'],
            'po_status' => ['nullable', 'string', 'in:pending,ordered,partial,closed,cancelled'],
            'po_keterangan' => ['nullable', 'string'],
            'po_subtotal' => ['nullable', 'numeric', 'min:0'],
            'po_discount' => ['nullable', 'numeric', 'min:0'],
            'po_discount_type' => ['nullable', 'string', 'in:nominal,percent'],
            'po_discount_note' => ['nullable', 'string'],
            'po_ppn' => ['nullable', 'numeric', 'min:0'],
            'po_ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'po_pph' => ['nullable', 'numeric', 'min:0'],
            'po_pph_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'po_grand_total' => ['nullable', 'numeric', 'min:0'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.po_detail_id' => ['nullable', 'integer'],
            'details.*.po_detail_id_product' => ['required', 'exists:catalog_products,id'],
            'details.*.po_detail_qty' => ['required', 'integer', 'min:1'],
            'details.*.po_detail_harga' => ['nullable', 'numeric', 'min:0'],
            'details.*.po_detail_keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
