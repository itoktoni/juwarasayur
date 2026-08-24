<?php

namespace Modules\Chatbot\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Modules\Faq\Models\Faq;
use Stringable;

/**
 * AI tool: cari jawaban di knowledge base FAQ (dikelola admin).
 */
class SearchFaqTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Cari jawaban resmi dari daftar FAQ toko (jam buka, pengiriman, pembayaran, kebijakan, dll). Selalu gunakan tool ini sebelum menjawab pertanyaan umum tentang toko. Hasil berisi pertanyaan + jawaban resmi.';
    }

    public function handle(Request $request): Stringable|string
    {
        $keyword = trim((string) $request->string('keyword', ''));

        $faqs = Faq::query()
            ->where('is_active', true)
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($sub) use ($keyword) {
                    $sub->where('question', 'like', "%{$keyword}%")
                        ->orWhere('answer', 'like', "%{$keyword}%");
                });
            })
            ->orderByDesc('id')
            ->limit(5)
            ->get(['question', 'answer']);

        if ($faqs->isEmpty()) {
            return 'Tidak ada FAQ yang cocok dengan "'.$keyword.'".';
        }

        return "Jawaban resmi dari FAQ toko:\n\n".$faqs
            ->map(fn ($f) => "T: {$f->question}\nJ: {$f->answer}")
            ->implode("\n\n");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->description('Kata kunci dari pertanyaan customer, misal "jam buka" atau "pengiriman"'),
        ];
    }
}
