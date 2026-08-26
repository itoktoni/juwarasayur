@php
    $site = \App\Models\WebsiteSetting::merged();
    $fmt = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => rtrim(rtrim((string) $v, '0'), '.');
    // Ukuran kertas dari settings (.env STRUK_PAPER_WIDTH): 58 atau 80
    $paper = (int) config('printer.web.paper_width', 80) === 58 ? 58 : 80;
    $contentW = $paper === 58 ? 52 : 72;
    $cutW = $paper === 58 ? 56 : 76;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Continues PO ({{ $list->count() }})</title>
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
            width: {{ $contentW }}mm;
            padding: 2mm 1mm;
        }
        .struk .center { text-align: center; }
        .struk .store-name { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .struk .rows div { display: flex; justify-content: space-between; gap: 2mm; }
        .struk table.items { width: 100%; border-collapse: collapse; margin: 2mm 0; }
        .struk table.items td { vertical-align: top; padding: 0.4mm 0; }
        .struk td.r { text-align: right; white-space: nowrap; }
        .struk .grand {
            display: flex; justify-content: space-between;
            border-top: 1px dashed #000; border-bottom: 1px double #000;
            padding: 1mm 0; margin-top: 1mm;
            font-size: 13px; font-weight: bold;
        }
        .struk .footer { text-align: center; margin-top: 3mm; }

        /* Garis potong antar struk */
        .cut-line {
            width: {{ $cutW }}mm;
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

        @page { size: {{ $paper }}mm auto; margin: 0; }

        @media print {
            .toolbar { display: none; }
            body { padding-bottom: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">🖨️ Print ({{ $list->count() }} PO)</button>
        <a class="btn-back" href="{{ route('po-po.getTable') }}">← Kembali</a>
    </div>

    @foreach($list as $po)
        @if(! $loop->first)
            {{-- Pembatas garis potong antar struk --}}
            <div class="cut-line">✂ - - - - - - - - - - - - - - - - - - - - - - - - - - - - ✂</div>
        @endif

        <div class="struk">
            <div class="center">
                <div class="store-name">{{ $site['name'] ?? config('app.name') }}</div>
                @if(!empty($site['alamat']))<div>{{ $site['alamat'] }}</div>@endif
                @if(!empty($site['telepon']))<div>Telp: {{ $site['telepon'] }}</div>@endif
                <div style="margin-top: 1mm;">** PURCHASE ORDER **</div>
            </div>

            <div style="border-top: 1px dashed #000; margin: 2mm 0;"></div>

            <div>No&nbsp;&nbsp;&nbsp;: {{ $po->po_code }}</div>
            <div>Tgl&nbsp;&nbsp;&nbsp;: {{ \Illuminate\Support\Carbon::parse($po->po_tanggal)->format('d/m/Y') }}</div>
            <div>Supl&nbsp;&nbsp;: {{ $po->has_supplier?->supplier_nama ?? '-' }}</div>
            @if($po->po_keterangan)<div>Ket&nbsp;&nbsp;&nbsp;: {{ \Illuminate\Support\Str::limit($po->po_keterangan, 60) }}</div>@endif

            <table class="items">
                @foreach($po->has_details as $d)
                    <tr>
                        <td colspan="2">{{ $loop->iteration }}. {{ $d->has_product?->product_nama ?? '-' }}@if($d->po_detail_keterangan) <i>({{ \Illuminate\Support\Str::limit($d->po_detail_keterangan, 30) }})</i>@endif</td>
                    </tr>
                    <tr>
                        <td>&nbsp;&nbsp;&nbsp;{{ $d->po_detail_qty }} x {{ $fmt($d->po_detail_harga) }}</td>
                        <td class="r">{{ $fmt((int) $d->po_detail_qty * (float) $d->po_detail_harga) }}</td>
                    </tr>
                @endforeach
            </table>

            <div class="rows">
                <div><span>Subtotal</span><span>{{ $fmt($po->po_subtotal) }}</span></div>
                @if((float) $po->po_discount > 0)
                    @php $labelDiskon = $po->po_discount_type === 'percent' ? 'Diskon '.$pct($po->po_discount).'%' : 'Diskon'; @endphp
                    <div><span>{{ $labelDiskon }}</span><span>-{{ $fmt($po->po_discount_amount) }}</span></div>
                @endif
                @if((float) $po->po_ppn > 0)
                    <div><span>PPN {{ $pct($po->po_ppn_rate) }}%</span><span>{{ $fmt($po->po_ppn) }}</span></div>
                @endif
                @if((float) $po->po_pph > 0)
                    <div><span>PPh {{ $pct($po->po_pph_rate) }}%</span><span>{{ $fmt($po->po_pph) }}</span></div>
                @endif
            </div>

            <div class="grand"><span>TOTAL</span><span>Rp {{ $fmt($po->po_grand_total) }}</span></div>

            <div class="footer">
                ~~ Terima kasih ~~
            </div>
            <div class="titik" style="margin-top: 10px;">
                .
            </div>
        </div>
    @endforeach

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
