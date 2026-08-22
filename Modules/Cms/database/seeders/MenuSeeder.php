<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cms\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        Menu::updateOrCreate(
            ['slug' => 'menu-footer'],
            [
                'name' => 'Menu Footer',
                'location' => 'footer',
                'is_active' => true,
                'sort_order' => 0,
                'items' => [
                    [
                        'label' => 'Jelajahi',
                        'url' => '#',
                        'icon' => null,
                        'target' => '_self',
                        'sort_order' => 1,
                        'children' => $this->links([
                            ['Beranda', '/'],
                            ['Belanja', '/product'],
                            ['Blog', route('blog')],
                        ]),
                    ],
                    [
                        'label' => 'Akun Saya',
                        'url' => '#',
                        'icon' => null,
                        'target' => '_self',
                        'sort_order' => 2,
                        'children' => $this->links([
                            ['Keranjang', '/cart'],
                            ['Checkout', '/checkout'],
                            ['Pesanan Saya', '/account/orders'],
                            ['Masuk / Daftar', '/login'],
                        ]),
                    ],
                    [
                        'label' => 'Bantuan',
                        'url' => '#',
                        'icon' => null,
                        'target' => '_self',
                        'sort_order' => 3,
                        'children' => $this->links([
                            ['Hubungi Kami', '/contact'],
                            ['Pencarian', '/search'],
                        ]),
                    ],
                ],
            ]
        );
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $pairs
     * @return array<int, array{label: string, url: string, target: string}>
     */
    private function links(array $pairs): array
    {
        return array_map(fn ($pair) => [
            'label' => $pair[0],
            'url' => $pair[1],
            'target' => '_self',
        ], $pairs);
    }
}
