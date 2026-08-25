<?php

namespace Modules\So\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;

#[Fillable(['so_code', 'so_payment_token', 'so_tanggal', 'so_id_reseller', 'so_id_customer', 'so_customer_name', 'so_customer_phone', 'so_status', 'so_shipping_method', 'so_cod_location', 'so_shipping_fee', 'so_distance_km', 'so_address', 'so_lat', 'so_lng', 'so_keterangan', 'so_subtotal', 'so_discount', 'so_discount_type', 'so_discount_note', 'so_dpp', 'so_ppn', 'so_ppn_rate', 'so_pph', 'so_pph_rate', 'so_grand_total', 'so_po_generated_at'])]
class So extends BaseModel
{
    protected $table = 'so_orders';

    public static $sortColumns = ['so_code', 'so_tanggal', 'so_status', 'so_grand_total'];

    public static $filterColumns = [
        'so_code' => 'Kode SO',
        'so_status' => 'Status',
        'so_shipping_method' => 'Pengiriman',
        'so_discount_note' => 'Keterangan Diskon',
    ];

    protected function casts(): array
    {
        return [
            'so_tanggal' => 'date',
            'so_po_generated_at' => 'datetime',
            'so_subtotal' => 'decimal:2',
            'so_discount' => 'decimal:2',
            'so_dpp' => 'decimal:2',
            'so_ppn' => 'decimal:2',
            'so_ppn_rate' => 'decimal:2',
            'so_pph' => 'decimal:2',
            'so_pph_rate' => 'decimal:2',
            'so_shipping_fee' => 'decimal:2',
            'so_distance_km' => 'decimal:2',
            'so_grand_total' => 'decimal:2',
        ];
    }

    protected $attributes = [
        'so_status' => SoStatusEnum::PENDING,
        'so_shipping_method' => ShippingMethodEnum::PICKUP,
        'so_subtotal' => 0,
        'so_discount' => 0,
        'so_discount_type' => 'nominal',
        'so_dpp' => 0,
        'so_ppn' => 0,
        'so_ppn_rate' => 0,
        'so_pph' => 0,
        'so_pph_rate' => 0,
        'so_shipping_fee' => 0,
        'so_grand_total' => 0,
    ];

    public static function field_name(): string
    {
        return 'so_code';
    }

    public function has_reseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'so_id_reseller');
    }

    public function has_customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'so_id_customer');
    }

    public function has_details(): HasMany
    {
        return $this->hasMany(SoDetail::class, 'so_detail_id_so');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->so_code)) {
                $model->so_code = static::generateCode();
            }

            // URL pembayaran memakai token acak, bukan id berurutan
            $model->so_payment_token ??= (string) Str::uuid();
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'SO-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('so_code', $code)->exists());

        return $code;
    }

    /**
     * Hitung ulang subtotal & grand total dari detail.
     * Grand total = (subtotal - diskon) + ppn + pph + ongkir.
     */
    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->has_details()->get()->sum(fn ($d) => (int) $d->so_detail_qty * (float) $d->so_detail_harga);

        $totals = static::calculateTotals(
            $subtotal,
            (float) ($this->so_discount ?? 0),
            (string) ($this->so_discount_type ?? 'nominal'),
            (float) ($this->so_ppn_rate ?? 0),
            (float) ($this->so_pph_rate ?? 0),
            (float) ($this->so_shipping_fee ?? 0)
        );

        $this->updateQuietly([
            'so_subtotal' => $totals['subtotal'],
            'so_dpp' => $totals['dpp'],
            'so_ppn' => $totals['ppn'],
            'so_pph' => $totals['pph'],
            'so_grand_total' => $totals['grand_total'],
        ]);
    }

    public static function calculateTotals(float $subtotal, float $discount, string $discountType, float $ppnRate, float $pphRate, float $shippingFee = 0): array
    {
        $discountAmount = $discountType === 'percent'
            ? min($subtotal * $discount / 100, $subtotal)
            : min(max($discount, 0), $subtotal);

        $dpp = max(0, $subtotal - $discountAmount);
        $ppn = $dpp * $ppnRate / 100;
        $pph = $dpp * $pphRate / 100;
        // Ongkir tidak kena pajak
        $grandTotal = $dpp + $ppn + $pph + $shippingFee;

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'dpp' => $dpp,
            'ppn' => $ppn,
            'ppn_rate' => $ppnRate,
            'pph' => $pph,
            'pph_rate' => $pphRate,
            'shipping_fee' => $shippingFee,
            'grand_total' => $grandTotal,
        ];
    }

    public function rules(): array
    {
        return [
            'so_tanggal' => ['required', 'date'],
            // Diisi otomatis dari user login di controller jika kosong
            'so_id_reseller' => ['nullable', 'exists:users,id'],
            'so_id_customer' => ['nullable', 'exists:users,id'],
            'so_status' => ['nullable', 'string', 'in:'.implode(',', SoStatusEnum::getValues())],
            'so_shipping_method' => ['required', 'string', 'in:'.implode(',', ShippingMethodEnum::getValues())],
            'so_cod_location' => ['nullable', 'required_if:so_shipping_method,'.ShippingMethodEnum::COD, 'string', 'max:255'],
            'so_address' => ['nullable', 'required_if:so_shipping_method,'.ShippingMethodEnum::DELIVERY, 'string'],
            'so_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'so_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'so_keterangan' => ['nullable', 'string'],
            // Diskon & pajak (opsional)
            'so_discount' => ['nullable', 'numeric', 'min:0'],
            'so_discount_type' => ['nullable', 'string', 'in:nominal,percent'],
            'so_discount_note' => ['nullable', 'string', 'max:500'],
            'so_ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'so_pph_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.so_detail_id_product' => ['required', 'exists:catalog_products,id'],
            'details.*.so_detail_qty' => ['required', 'integer', 'min:1'],
            'details.*.so_detail_harga' => ['nullable', 'numeric', 'min:0'],
            'details.*.so_detail_keterangan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
