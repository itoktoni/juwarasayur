<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu Configuration
    |--------------------------------------------------------------------------
    |
    | Define menu items for desktop sidebar, mobile drawer, and bottom nav.
    | Each item: route (string), icon (string), label (string)
    | Sections: label (string), items (array)
    | Bottom nav: only 5 items max, uses short label
    |
    */

    'sidebar' => [
        [
            'label' => 'Toko',
            'items' => [
                ['route' => 'shop.index', 'icon' => 'storefront', 'label' => 'Belanja', 'match' => ['shop.*']],
                ['route' => 'cart.index', 'icon' => 'shopping_cart', 'label' => 'Keranjang', 'match' => ['cart.*', 'checkout.*']],
                ['route' => 'ecommerce.orders.index', 'icon' => 'receipt_long', 'label' => 'Pesanan Saya', 'match' => ['ecommerce.orders.*']],
            ],
        ],
        [
            'label' => null,
            'items' => [
                ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => [
                ['route' => 'user.getTable', 'icon' => 'manage_accounts', 'label' => 'Users', 'match' => ['user.*']],
                ['route' => 'shipping.getTable', 'icon' => 'local_shipping', 'label' => 'Pengiriman & COD', 'match' => ['shipping.*']],
            ],
        ],
        [
            'label' => 'Catalog',
            'items' => [
                ['route' => 'catalog-product.getTable', 'icon' => 'inventory_2', 'label' => 'Products', 'match' => ['catalog-product.*']],
                ['route' => 'catalog-category.getTable', 'icon' => 'category', 'label' => 'Categories', 'match' => ['catalog-category.*']],
                ['route' => 'catalog-brand.getTable', 'icon' => 'branding_watermark', 'label' => 'Brands', 'match' => ['catalog-brand.*']],
                ['route' => 'catalog-satuan.getTable', 'icon' => 'straighten', 'label' => 'Satuan', 'match' => ['catalog-satuan.*']],
                ['route' => 'catalog-tag.getTable', 'icon' => 'label', 'label' => 'Tags', 'match' => ['catalog-tag.*']],
            ],
        ],
        [
            'label' => 'Sales',
            'items' => [
                ['route' => 'so-so.getTable', 'icon' => 'receipt_long', 'label' => 'Sales Orders', 'match' => ['so-so.*']],
                ['route' => 'so-customer.getTable', 'icon' => 'group', 'label' => 'Customers', 'match' => ['so-customer.*']],
            ],
        ],
        [
            'label' => 'Purchase',
            'items' => [
                ['route' => 'po-supplier.getTable', 'icon' => 'local_shipping', 'label' => 'Suppliers', 'match' => ['po-supplier.*']],
                ['route' => 'po-po.getTable', 'icon' => 'shopping_cart', 'label' => 'Purchase Orders', 'match' => ['po-po.*', 'po-detail.*']],
            ],
        ],
        [
            'label' => 'Inventory',
            'items' => [
                ['route' => 'inventory-gudang.getTable', 'icon' => 'warehouse', 'label' => 'Warehouse', 'match' => ['inventory-gudang.*']],
                ['route' => 'inventory-lokasi.getTable', 'icon' => 'location_on', 'label' => 'Lokasi', 'match' => ['inventory-lokasi.*']],
                ['route' => 'inventory-stock.getTable', 'icon' => 'inventory', 'label' => 'Stock', 'match' => ['inventory-stock.*']],
            ],
        ],
        [
            'label' => 'CMS',
            'items' => [
                ['route' => 'cms-type.getTable', 'icon' => 'category', 'label' => 'Types', 'match' => ['cms-type.*']],
                ['route' => 'field.getTable', 'icon' => 'input', 'label' => 'Fields', 'match' => ['field.*']],
                ['route' => 'section.getTable', 'icon' => 'view_agenda', 'label' => 'Sections', 'match' => ['section.*']],
                ['route' => 'content.getTable', 'icon' => 'article', 'label' => 'Content', 'match' => ['content.*']],
                ['route' => 'category.getTable', 'icon' => 'sell', 'label' => 'Categories', 'match' => ['category.*']],
                ['route' => 'tag.getTable', 'icon' => 'label', 'label' => 'Tags', 'match' => ['tag.*']],
                ['route' => 'menu.getTable', 'icon' => 'menu', 'label' => 'Menus', 'match' => ['menu.*']],
            ],
        ],
        [
            'label' => 'Settings',
            'items' => [
                ['route' => 'settings.website', 'icon' => 'language', 'label' => 'Website'],
                ['route' => 'settings.env', 'icon' => 'settings', 'label' => 'Environment'],
                ['route' => 'native-bridge-test', 'icon' => 'phone_android', 'label' => 'NativeBridge Test'],
            ],
        ],
    ],

    'bottom_nav' => [

        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Left'],
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Kiri'],
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Home'],
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Kanan'],
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Right'],

    ],

];
