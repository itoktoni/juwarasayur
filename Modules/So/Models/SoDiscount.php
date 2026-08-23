<?php

namespace Modules\So\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['discount_code', 'discount_nama', 'discount_type', 'discount_value', 'discount_min_purchase', 'is_active'])]
class SoDiscount extends BaseModel
{
    public const TYPE_PERCENT = 'percent';

    public const TYPE_NOMINAL = 'nominal';

    protected $table = 'so_discounts';

    protected $attributes = [
        'discount_type' => self::TYPE_PERCENT,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'discount_min_purchase' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'discount_code';
    }

    public static $sortColumns = ['discount_code', 'discount_nama', 'discount_value'];
    public static $filterColumns = ['discount_code' => 'Kode', 'discount_nama' => 'Nama'];

    /**
     * Hitung potongan untuk subtotal tertentu (matrix min purchase divalidasi terpisah).
     */
    public function hitungPotongan(float $subtotal): float
    {
        if ($this->discount_type === self::TYPE_PERCENT) {
            return round($subtotal * (float) $this->discount_value / 100, 2);
        }

        return min((float) $this->discount_value, $subtotal);
    }

    /**
     * Validasi matrix: aktif & subtotal memenuhi minimal transaksi.
     */
    public function layakDigunakan(float $subtotal): bool
    {
        return $this->is_active && $subtotal >= (float) $this->discount_min_purchase;
    }

    public function rules(): array
    {
        return [
            'discount_code' => ['required', 'string', 'max:50', 'unique:so_discounts,discount_code'.($this->exists ? ','.$this->id : '')],
            'discount_nama' => ['required', 'string', 'max:100'],
            'discount_type' => ['required', 'in:'.self::TYPE_PERCENT.','.self::TYPE_NOMINAL],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'discount_min_purchase' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
