<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Catalog: catalog_products ──
        if (Schema::hasTable('catalog_products')) {
            Schema::table('catalog_products', function (Blueprint $table) {
                $table->unsignedBigInteger('product_harga')->default(0)->change();
                $table->unsignedBigInteger('product_harga_modal')->default(0)->change();
                $table->unsignedBigInteger('product_harga_grosir')->nullable()->change();
                $table->unsignedInteger('product_berat')->nullable()->change();
            });

            DB::table('catalog_products')->update([
                'product_harga' => DB::raw('ROUND(product_harga)'),
                'product_harga_modal' => DB::raw('ROUND(product_harga_modal)'),
                'product_harga_grosir' => DB::raw('ROUND(product_harga_grosir)'),
                'product_berat' => DB::raw('ROUND(product_berat)'),
            ]);
        }

        // ── SO: so_orders ──
        if (Schema::hasTable('so_orders')) {
            Schema::table('so_orders', function (Blueprint $table) {
                $table->unsignedBigInteger('so_shipping_fee')->default(0)->change();
                $table->unsignedInteger('so_distance_km')->nullable()->change();
                $table->unsignedBigInteger('so_subtotal')->default(0)->change();
                $table->unsignedBigInteger('so_discount')->default(0)->change();
                $table->unsignedBigInteger('so_dpp')->default(0)->change();
                $table->unsignedBigInteger('so_ppn')->default(0)->change();
                $table->unsignedSmallInteger('so_ppn_rate')->default(0)->change();
                $table->unsignedBigInteger('so_pph')->default(0)->change();
                $table->unsignedSmallInteger('so_pph_rate')->default(0)->change();
                $table->unsignedBigInteger('so_grand_total')->default(0)->change();
                $table->unsignedBigInteger('so_unique_amount')->nullable()->change();
            });

            DB::table('so_orders')->update([
                'so_shipping_fee' => DB::raw('ROUND(so_shipping_fee)'),
                'so_subtotal' => DB::raw('ROUND(so_subtotal)'),
                'so_discount' => DB::raw('ROUND(so_discount)'),
                'so_dpp' => DB::raw('ROUND(so_dpp)'),
                'so_ppn' => DB::raw('ROUND(so_ppn)'),
                'so_pph' => DB::raw('ROUND(so_pph)'),
                'so_grand_total' => DB::raw('ROUND(so_grand_total)'),
                'so_unique_amount' => DB::raw('ROUND(so_unique_amount)'),
            ]);
        }

        // ── SO: so_order_details ──
        if (Schema::hasTable('so_order_details')) {
            Schema::table('so_order_details', function (Blueprint $table) {
                $table->unsignedBigInteger('so_detail_harga')->default(0)->change();
            });

            DB::table('so_order_details')->update([
                'so_detail_harga' => DB::raw('ROUND(so_detail_harga)'),
            ]);

            if (Schema::hasColumn('so_order_details', 'fee_percent')) {
                Schema::table('so_order_details', function (Blueprint $table) {
                    $table->unsignedSmallInteger('fee_percent')->nullable()->change();
                    $table->unsignedBigInteger('fee_amount')->default(0)->change();
                });

                DB::table('so_order_details')->update([
                    'fee_amount' => DB::raw('ROUND(fee_amount)'),
                ]);
            }
        }

        // ── SO: so_discounts ──
        if (Schema::hasTable('so_discounts')) {
            Schema::table('so_discounts', function (Blueprint $table) {
                $table->unsignedBigInteger('discount_value')->default(0)->change();
                $table->unsignedBigInteger('discount_min_purchase')->default(0)->change();
            });

            DB::table('so_discounts')->update([
                'discount_value' => DB::raw('ROUND(discount_value)'),
                'discount_min_purchase' => DB::raw('ROUND(discount_min_purchase)'),
            ]);
        }

        // ── SO: consignments ──
        if (Schema::hasTable('consignments')) {
            Schema::table('consignments', function (Blueprint $table) {
                $table->unsignedInteger('total_qty')->default(0)->change();
                $table->unsignedInteger('total_sold')->default(0)->change();
                $table->unsignedInteger('total_returned')->default(0)->change();
                $table->unsignedBigInteger('total_amount')->default(0)->change();
            });

            DB::table('consignments')->update([
                'total_qty' => DB::raw('ROUND(total_qty)'),
                'total_sold' => DB::raw('ROUND(total_sold)'),
                'total_returned' => DB::raw('ROUND(total_returned)'),
                'total_amount' => DB::raw('ROUND(total_amount)'),
            ]);
        }

        // ── SO: consignment_details ──
        if (Schema::hasTable('consignment_details')) {
            Schema::table('consignment_details', function (Blueprint $table) {
                $table->unsignedInteger('qty')->default(0)->change();
                $table->unsignedInteger('qty_sold')->nullable()->change();
                $table->unsignedInteger('qty_returned')->nullable()->change();
                $table->unsignedBigInteger('price')->default(0)->change();
            });

            DB::table('consignment_details')->update([
                'qty' => DB::raw('ROUND(qty)'),
                'qty_sold' => DB::raw('ROUND(qty_sold)'),
                'qty_returned' => DB::raw('ROUND(qty_returned)'),
                'price' => DB::raw('ROUND(price)'),
            ]);
        }

        // ── Ecommerce: so_cod_locations ──
        if (Schema::hasTable('so_cod_locations')) {
            Schema::table('so_cod_locations', function (Blueprint $table) {
                $table->unsignedBigInteger('fee')->nullable()->change();
            });

            DB::table('so_cod_locations')->update([
                'fee' => DB::raw('ROUND(fee)'),
            ]);
        }

        // ── Reseller: withdrawals ──
        if (Schema::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->unsignedBigInteger('amount')->change();
            });

            DB::table('withdrawals')->update([
                'amount' => DB::raw('ROUND(amount)'),
            ]);
        }

        // ── Users: fee ──
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'fee')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedSmallInteger('fee')->nullable()->change();
            });

            DB::table('users')->whereNotNull('fee')->update([
                'fee' => DB::raw('ROUND(fee)'),
            ]);
        }

        // ── PO: po_pos ──
        if (Schema::hasTable('po_pos')) {
            Schema::table('po_pos', function (Blueprint $table) {
                $table->unsignedBigInteger('po_subtotal')->default(0)->change();
                $table->unsignedBigInteger('po_discount')->default(0)->change();
                $table->unsignedBigInteger('po_dpp')->default(0)->change();
                $table->unsignedBigInteger('po_ppn')->default(0)->change();
                $table->unsignedSmallInteger('po_ppn_rate')->default(0)->change();
                $table->unsignedBigInteger('po_pph')->default(0)->change();
                $table->unsignedSmallInteger('po_pph_rate')->default(0)->change();
                $table->unsignedBigInteger('po_grand_total')->default(0)->change();
            });

            DB::table('po_pos')->update([
                'po_subtotal' => DB::raw('ROUND(po_subtotal)'),
                'po_discount' => DB::raw('ROUND(po_discount)'),
                'po_dpp' => DB::raw('ROUND(po_dpp)'),
                'po_ppn' => DB::raw('ROUND(po_ppn)'),
                'po_pph' => DB::raw('ROUND(po_pph)'),
                'po_grand_total' => DB::raw('ROUND(po_grand_total)'),
            ]);
        }

        // ── PO: po_details ──
        if (Schema::hasTable('po_details')) {
            Schema::table('po_details', function (Blueprint $table) {
                $table->unsignedBigInteger('po_detail_harga')->default(0)->change();
                $table->unsignedBigInteger('po_detail_subtotal')->default(0)->change();
            });

            DB::table('po_details')->update([
                'po_detail_harga' => DB::raw('ROUND(po_detail_harga)'),
                'po_detail_subtotal' => DB::raw('ROUND(po_detail_subtotal)'),
            ]);
        }

        // ── Production: production_costs ──
        if (Schema::hasTable('production_costs')) {
            Schema::table('production_costs', function (Blueprint $table) {
                $table->unsignedBigInteger('production_cost_nominal')->default(0)->change();
            });

            DB::table('production_costs')->update([
                'production_cost_nominal' => DB::raw('ROUND(production_cost_nominal)'),
            ]);
        }
    }

    public function down(): void
    {
        // Forward-only migration.
    }
};
