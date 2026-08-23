@php
    $site = \App\Models\WebsiteSetting::merged();
    $paid = $so->so_status === \Modules\So\Enums\SoStatusEnum::PAID;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $so->so_code }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #111; padding: 24px; font-size: 14px; }
        .sheet { max-width: 640px; margin: 0 auto; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111; padding-bottom: 12px; }
        .store { font-size: 20px; font-weight: 800; }
        .store small { display: block; font-size: 11px; font-weight: 400; color: #555; margin-top: 4px; white-space: pre-line; }
        .inv { text-align: right; }
        .inv h1 { font-size: 18px; letter-spacing: 2px; }
        .inv p { font-size: 12px; color: #444; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.items th, table.items td { text-align: left; padding: 8px 6px; border-bottom: 1px solid #ddd; }
        table.items th { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #555; }
        td.num, th.num { text-align: right; }
        .totals { margin-top: 12px; margin-left: auto; width: 260px; }
        .totals div { display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; }
        .totals .grand { border-top: 2px solid #111; margin-top: 6px; padding-top: 8px; font-size: 16px; font-weight: 800; }
        .meta { margin-top: 16px; display: grid; grid-template-columns: 140px 1fr; gap: 4px 12px; font-size: 13px; background: #f7f7f7; border-radius: 8px; padding: 12px; }
        .meta span:nth-child(odd) { color: #555; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; }
        .badge.paid { background: #dcfce7; color: #166534; }
        .badge.pending { background: #fef9c3; color: #854d0e; }
        .toolbar { max-width: 640px; margin: 0 auto 16px; display: flex; gap: 8px; }
        .btn { padding: 10px 18px; border-radius: 8px; border: 0; cursor: pointer; font-size: 14px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-primary { background: {{ $site['colors']['primary'] ?? '#00288e' }}; color: #fff; }
        .btn-soft { background: #eee; color: #333; }
        footer { margin-top: 24px; text-align: center; font-size: 11px; color: #888; }
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print Invoice</button>
        <a href="{{ route('payment.show', ['token' => $so->so_payment_token]) }}" class="btn btn-soft">← Kembali</a>
    </div>

    <div class="sheet">
        <div class="head">
            <div class="store">
                {{ $site['name'] ?? config('app.name') }}
                @if(!empty($site['alamat']))<small>{{ $site['alamat'] }}@if(!empty($site['telepon'])) — {{ $site['telepon'] }}@endif</small>@endif
            </div>
            <div class="inv">
                <h1>INVOICE</h1>
                <p class="font-mono">{{ $so->so_code }}</p>
                <p>{{ \Illuminate\Support\Carbon::parse($so->so_tanggal)->format('d/m/Y H:i') }}</p>
                <span class="badge {{ $paid ? 'paid' : 'pending' }}">{{ $paid ? 'LUNAS' : 'PENDING' }}</span>
            </div>
        </div>

        <table class="items">
            <thead>
                <tr><th>Produk</th><th class="num">Qty</th><th class="num">Harga</th><th class="num">Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($so->has_details as $d)
                    <tr>
                        <td>{{ $d->has_product?->product_nama ?? '-' }}</td>
                        <td class="num">{{ $d->so_detail_qty }}</td>
                        <td class="num">{{ formatAngka((float) $d->so_detail_harga, 'Rp') }}</td>
                        <td class="num">{{ formatAngka((int) $d->so_detail_qty * (float) $d->so_detail_harga, 'Rp') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>Subtotal</span><span>{{ formatAngka((float) $so->so_subtotal, 'Rp') }}</span></div>
            @if((float) $so->so_shipping_fee > 0)
                <div><span>Ongkir</span><span>{{ formatAngka((float) $so->so_shipping_fee, 'Rp') }}</span></div>
            @endif
            <div class="grand"><span>Total</span><span>{{ formatAngka((float) $so->so_grand_total, 'Rp') }}</span></div>
        </div>

        <div class="meta">
            <span>Metode Pengiriman</span><span>{{ $methodLabel }}{{ $so->so_cod_location ? ' — '.$so->so_cod_location : '' }}</span>
            @if($so->so_address)<span>Alamat</span><span>{{ $so->so_address }}</span>@endif
            <span>Pemesan</span><span>{{ $so->so_customer_name }} ({{ $so->so_customer_phone }})</span>
        </div>

        <footer>Terima kasih telah berbelanja — invoice ini dibuat otomatis.</footer>
    </div>

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
