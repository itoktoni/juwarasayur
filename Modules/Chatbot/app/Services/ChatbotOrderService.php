<?php

namespace Modules\Chatbot\Services;

use Illuminate\Support\Facades\DB;
use Modules\Chatbot\Models\ChatbotSession;
use Modules\So\Enums\ShippingMethodEnum;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

/**
 * Converts a chatbot cart ([product_id => qty]) into a So (so_orders) header
 * + SoDetail (so_order_details) rows, then returns the QRIS payment URL.
 *
 * Reuses the exact same tables/statuses as the web checkout flow.
 */
class ChatbotOrderService
{
    public function __construct(private CatalogService $catalog) {}

    /**
     * @param  array<int, int>  $cart  [product_id => qty]
     * @param  array{method: string, fee?: float, address?: ?string, cod_location?: ?string, distance_km?: ?float, lat?: ?float, lng?: ?float}  $shipping
     * @return array{so: So, payment_url: string, subtotal: float, grand_total: float}
     */
    public function createOrder(ChatbotSession $session, array $cart, array $shipping = []): array
    {
        $products = $this->catalog->findByIds(array_keys($cart));
        $items = [];

        foreach ($cart as $productId => $qty) {
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $items[] = [
                'product' => $product,
                'qty' => (int) $qty,
                'price' => (float) $product->product_harga,
            ];
        }

        if (empty($items)) {
            throw new \RuntimeException('Keranjang chatbot kosong.');
        }

        // Metode kirim wajib dipilih customer sebelum checkout.
        $method = ShippingMethodEnum::coerce($shipping['method'] ?? null);
        if ($method === null) {
            throw new \RuntimeException('Metode pengiriman belum dipilih.');
        }

        $shippingFee = (float) ($shipping['fee'] ?? 0);

        $subtotal = array_sum(array_map(fn ($i) => $i['qty'] * $i['price'], $items));
        $totals = So::calculateTotals($subtotal, 0, 'nominal', 0, 0, $shippingFee);

        $so = DB::transaction(function () use ($session, $items, $totals, $method, $shipping) {
            $so = So::create([
                'so_tanggal' => now(),
                // Pesanan via chatbot tidak selalu punya akun — id user diisi jika ada.
                'so_id_reseller' => null,
                'so_id_customer' => $session->user_id,
                'so_customer_name' => $session->contact_name ?: 'Customer Chatbot',
                'so_customer_phone' => $session->contact_phone,
                'so_status' => SoStatusEnum::PENDING,
                'so_shipping_method' => $method,
                'so_cod_location' => $method === ShippingMethodEnum::COD ? ($shipping['cod_location'] ?? null) : null,
                'so_address' => $shipping['address'] ?? null,
                'so_lat' => $shipping['lat'] ?? null,
                'so_lng' => $shipping['lng'] ?? null,
                'so_distance_km' => $shipping['distance_km'] ?? null,
                'so_shipping_fee' => $totals['shipping_fee'],
                'so_subtotal' => $totals['subtotal'],
                'so_discount' => 0,
                'so_discount_type' => 'nominal',
                'so_ppn_rate' => 0,
                'so_pph_rate' => 0,
                'so_grand_total' => $totals['grand_total'],
            ]);

            $seq = 1;
            foreach ($items as $item) {
                SoDetail::create([
                    'so_detail_code' => sprintf('%s-%03d', $so->so_code, $seq),
                    'so_detail_id_so' => $so->id,
                    'so_detail_id_product' => $item['product']->id,
                    'so_detail_qty' => $item['qty'],
                    'so_detail_harga' => $item['price'],
                    'so_detail_keterangan' => 'Order via '.strtoupper($session->channel),
                ]);
                $seq++;
            }

            return $so;
        });

        return [
            'so' => $so,
            'payment_url' => route('payment.show', ['token' => $so->so_payment_token]),
            'subtotal' => $totals['subtotal'],
            'grand_total' => $totals['grand_total'],
        ];
    }
}
