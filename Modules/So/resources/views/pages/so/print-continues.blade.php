@php
    $site = \App\Models\WebsiteSetting::merged();
    $shippingLabel = fn ($so) => \Modules\So\Enums\ShippingMethodEnum::getDescription($so->so_shipping_method).($so->so_cod_location ? ' ('.$so->so_cod_location.')' : '');
    $fmt = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => rtrim(rtrim((string) $v, '0'), '.');
    $diskonAmount = fn ($so) => max(0, (float) $so->so_subtotal - (float) $so->so_dpp);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Continues Struk ({{ $list->count() }} SO)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { background: #fff; }
        body {
            font-family: 'Courier New', Courier, monospace;
            color: #000;
            font-size: 11px;
            line-height: 1.35;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .struk {
            width: 72mm;
            padding: 2mm 1mm;
        }
        .struk .center { text-align: center; }
        .struk .bold { font-weight: bold; }
        .struk .store-name { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .struk table.items { width: 100%; border-collapse: collapse; margin: 2mm 0; }
        .struk table.items td { vertical-align: top; padding: 0.4mm 0; }
        .struk td.r { text-align: right; white-space: nowrap; }
        .struk .rows div { display: flex; justify-content: space-between; gap: 2mm; }
        .struk .grand {
            display: flex; justify-content: space-between;
            border-top: 1px dashed #000; border-bottom: 1px double #000;
            padding: 1mm 0; margin-top: 1mm;
            font-size: 13px; font-weight: bold;
        }
        .struk .footer { text-align: center; margin-top: 3mm; }

        /* Garis potong antar struk */
        .cut-line {
            width: 76mm;
            text-align: center;
            padding: 2mm 0;
            white-space: nowrap;
            overflow: hidden;
            color: #000;
            letter-spacing: 1px;
            user-select: none;
        }

        /* Bottom bar fixed */
        .toolbar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
            display: flex; gap: 8px; justify-content: center; align-items: center;
            padding: 12px;
            background: #fff; border-top: 1px solid #d1d5db;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.08);
            font-family: system-ui, sans-serif; font-size: 14px;
        }
        body { padding-bottom: 72px; }
        .toolbar button {
            padding: 8px 18px; border: 0; border-radius: 8px; cursor: pointer;
            font-weight: 700; font-size: 14px;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-back { background: #e5e7eb; color: #374151; text-decoration: none; display: inline-flex; align-items: center; }

        @page { size: 80mm auto; margin: 0; }

        @media print {
            .toolbar { display: none; }
            body { padding-bottom: 0; }
            .cut-line::before,
            .cut-line::after { content: ''; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">🖨️ Print ({{ $list->count() }} struk)</button>
        <a class="btn-back" href="{{ route('so-so.getTable') }}">← Kembali</a>
    </div>

    @foreach($list as $i => $so)
        @if(! $loop->first)
            {{-- Pembatas garis potong antar struk --}}
            <div class="cut-line">✂ - - - - - - - - - - - - - - - - - - - - - - - - - - - - ✂</div>
        @endif

        <div class="struk">
            <div class="center">
                <div class="store-name">{{ $site['name'] ?? config('app.name') }}</div>
                @if(!empty($site['alamat']))<div>{{ $site['alamat'] }}</div>@endif
                @if(!empty($site['telepon']))<div>Telp: {{ $site['telepon'] }}</div>@endif
            </div>

            <div style="border-top: 1px dashed #000; margin: 2mm 0;"></div>

            <div>No&nbsp;&nbsp;: {{ $so->so_code }}</div>
            <div>Tgl&nbsp;&nbsp;: {{ \Illuminate\Support\Carbon::parse($so->so_tanggal)->format('d/m/Y H:i') }}</div>
            <div>Cs&nbsp;&nbsp;&nbsp;: {{ $so->has_customer?->name ?? $so->so_customer_name ?? '-' }}</div>
            <div>Res&nbsp;&nbsp;&nbsp;: {{ $so->has_reseller?->name ?? '-' }}</div>
            <div>Kirim: {{ $shippingLabel($so) }}</div>
            @if($so->so_address)<div>Almt&nbsp;: {{ \Illuminate\Support\Str::limit($so->so_address, 60) }}</div>@endif

            <table class="items">
                @foreach($so->has_details as $d)
                    <tr>
                        <td colspan="2">{{ $loop->iteration }}. {{ $d->has_product?->product_nama ?? '-' }}@if($d->so_detail_keterangan) <i>({{ \Illuminate\Support\Str::limit($d->so_detail_keterangan, 30) }})</i>@endif</td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{ $d->so_detail_qty }} x {{ number_format((float) $d->so_detail_harga, 0, ',', '.') }}</td>
                        <td class="r">{{ number_format((int) $d->so_detail_qty * (float) $d->so_detail_harga, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </table>

            <div class="rows">
                <div><span>Subtotal</span><span>{{ $fmt($so->so_subtotal) }}</span></div>
                @if((float) $so->so_discount > 0)
                    @php $labelDiskon = $so->so_discount_type === 'percent' ? 'Diskon '.$pct($so->so_discount).'%' : 'Diskon'; @endphp
                    <div><span>{{ $labelDiskon }}</span><span>-{{ $fmt($diskonAmount($so)) }}</span></div>
                @endif
                @if((float) $so->so_ppn > 0)
                    <div><span>PPN {{ $pct($so->so_ppn_rate) }}%</span><span>{{ $fmt($so->so_ppn) }}</span></div>
                @endif
                @if((float) $so->so_pph > 0)
                    <div><span>PPh {{ $pct($so->so_pph_rate) }}%</span><span>{{ $fmt($so->so_pph) }}</span></div>
                @endif
                @if((float) $so->so_shipping_fee > 0)
                    <div><span>Ongkir</span><span>{{ $fmt($so->so_shipping_fee) }}</span></div>
                @endif
            </div>

            <div class="grand"><span>TOTAL</span><span>Rp {{ $fmt($so->so_grand_total) }}</span></div>

            <div class="footer">
                ~~ Terima kasih ~~
            </div>
        </div>
    @endforeach

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
