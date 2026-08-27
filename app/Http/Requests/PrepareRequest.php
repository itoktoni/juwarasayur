<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrepareRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Admin middleware sudah filter di route
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:catalog_products,id'],
            'lokasi_id' => ['required', 'integer', 'exists:inv_lokasis,id'],
            'qty' => ['required', 'integer', 'min:1'],
            'expired_date' => ['nullable', 'date'],
            'so_detail_ids' => ['required', 'array', 'min:1'],
            'so_detail_ids.*' => ['integer', 'exists:so_order_details,id'],
        ];
    }
}
