<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cms\Models\Content;
use Modules\Cms\Models\Field;
use Modules\Cms\Models\Section;
use Modules\Cms\Models\Type;

/**
 * Blueprint + konten "homepage" agar homepage ecommerce
 * bisa dikustom dari admin CMS (/cms/content).
 */
class CmsHomepageSeeder extends Seeder
{
    public function run(): void
    {
        $type = Type::where('slug', 'homepage')->first();

        if (! $type) {
            $type = Type::create([
                'name' => 'Homepage',
                'slug' => 'homepage',
                'type' => 'custom',
                'description' => 'Pengaturan halaman depan ecommerce',
                'is_active' => true,
                'menu_position' => 0,
            ]);
        }

        $fields = [
            ['hero_title', 'Hero Judul', 'text'],
            ['hero_subtitle', 'Hero Subjudul', 'textarea'],
            ['hero_cta_text', 'Teks Tombol Hero', 'text'],
            ['flash_sale_title', 'Judul Flash Sale', 'text'],
            ['flash_sale_count', 'Jumlah Produk Flash Sale', 'number'],
            ['flash_sale_hours', 'Durasi Timer (jam)', 'number'],
            ['show_latest', 'Tampilkan Produk Terbaru', 'boolean'],
            ['latest_title', 'Judul Produk Terbaru', 'text'],
        ];

        $fieldIds = [];
        foreach ($fields as $i => [$name, $label, $fieldType]) {
            $field = Field::where('name', $name)->where('type_id', $type->id)->first();
            if (! $field) {
                $field = Field::create([
                    'name' => $name,
                    'label' => $label,
                    'type' => $fieldType,
                    'type_id' => $type->id,
                    'is_required' => false,
                    'sort_order' => $i,
                ]);
            }
            $fieldIds[] = $field->id;
        }

        $sections = [
            ['Hero', [$fieldIds[0], $fieldIds[1], $fieldIds[2]]],
            ['Flash Sale', [$fieldIds[3], $fieldIds[4], $fieldIds[5]]],
            ['Produk Terbaru', [$fieldIds[6], $fieldIds[7]]],
        ];

        foreach ($sections as $i => [$name, $ids]) {
            $section = Section::where('name', $name)->where('content_type_id', $type->id)->first();
            if (! $section) {
                Section::create([
                    'name' => $name,
                    'content_type_id' => $type->id,
                    'field_ids' => $ids,
                    'is_active' => true,
                    'sort_order' => $i,
                ]);
            } else {
                $section->update(['field_ids' => array_values(array_unique(array_merge($section->field_ids ?? [], $ids)))]);
            }
        }

        $defaults = [
            'hero_title' => 'Sayur & Sembako Segar, Langsung dari Gudang',
            'hero_subtitle' => 'Harga grosir untuk semua. Pesan mudah, ambil di gudang atau kirim ke lokasi Anda.',
            'hero_cta_text' => 'Mulai Belanja',
            'flash_sale_title' => 'Flash Sale',
            'flash_sale_count' => 6,
            'flash_sale_hours' => 12,
            'show_latest' => true,
            'latest_title' => 'Produk Terbaru',
        ];

        $content = Content::whereHas('has_type', fn ($q) => $q->where('slug', 'homepage'))->first();

        if (! $content) {
            Content::create([
                'content_type_id' => $type->id,
                'title' => 'Homepage Toko',
                'slug' => 'homepage',
                'status' => 'published',
                'published_at' => now(),
                'meta' => $defaults,
            ]);

            $this->command->info('Homepage CMS entry dibuat dengan default settings.');
        } else {
            // Isi meta yang belum ada saja (jangan menimpa kustomisasi user)
            $meta = $content->meta ?? [];
            $dirty = false;
            foreach ($defaults as $key => $value) {
                if (! isset($meta[$key])) {
                    $meta[$key] = $value;
                    $dirty = true;
                }
            }
            if ($dirty) {
                $content->update(['meta' => $meta]);
            }

            $this->command->info('Homepage CMS entry sudah ada.');
        }
    }
}
