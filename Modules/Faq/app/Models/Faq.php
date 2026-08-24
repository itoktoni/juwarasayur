<?php

namespace Modules\Faq\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\Fillable;

/**
 * FAQ = knowledge base untuk chatbot AI.
 * Pertanyaan/jawaban yang aktif otomatis jadi konteks jawaban asisten.
 */
#[Fillable(['question', 'answer', 'is_active'])]
class Faq extends BaseModel
{
    protected $table = 'faqs';

    public static $filterColumns = [
        'question' => 'Question',
        'is_active' => 'Active',
    ];

    public static $sortColumns = ['question', 'created_at'];

    public static function field_name(): string
    {
        return 'question';
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
