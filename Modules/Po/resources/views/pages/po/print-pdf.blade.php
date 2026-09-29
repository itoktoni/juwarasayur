@php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $tgl = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : '-';
    $sup = $po->has_supplier;
    $labelDiskon = $po->po_discount_type === 'percent' ? 'Diskon '.$pct($po->po_discount).'%' : 'Diskon';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>PO {{ $po->po_code }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 18px 16px 30px 16px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1a1a1a; padding: 10px 30px; }
        .kop { text-align: center; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; margin-bottom: 10px; }
        .kop h1 { font-size: 16px; text-transform: uppercase; letter-spacing: 1px; }
        .kop .sub { font-size: 10px; color: #555; margin-top: 2px; }
        .doc-title { text-align: center; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin: 6px 0 2px 0; }
        .doc-no { text-align: center; font-size: 11px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        .info td { vertical-align: top; padding: 1px 4px 1px 0; font-size: 10.5px; }
        .info .lbl { width: 78px; color: #555; }
        .info .sep { width: 10px; }
        .items { margin-top: 10px; }
        .items th { background: #1a1a1a; color: #fff; font-size: 10px; text-transform: uppercase; padding: 6px 8px; text-align: left; }
        .items td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; font-size: 10.5px; vertical-align: top; }
        .items tr:nth-child(even) td { background-color: #f7f7f7; }
        .c { text-align: center; }
        .r { text-align: right; white-space: nowrap; }
        .totals { margin-top: 8px; width: 100%; }
        .totals td { padding: 2px 8px; font-size: 10.5px; }
        .totals .grand td { border-top: 2px solid #1a1a1a; font-size: 13px; font-weight: bold; padding-top: 5px; }
        .ket { margin-top: 10px; font-size: 10px; color: #444; }
        .ttd { margin-top: 26px; }
        .ttd td { text-align: center; font-size: 10.5px; width: 33%; vertical-align: top; }
        .ttd .space { height: 56px; }
        .footer { margin-top: 12px; font-size: 9px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>{{ $site['name'] ?? config('app.name') }}</h1>
        @if(!empty($site['alamat']))<div class="sub">{{ $site['alamat'] }}</div>@endif
        @if(!empty($site['telepon']))<div class="sub">Telp: {{ $site['telepon'] }}</div>@endif
    </div>

    <div class="doc-title">Purchase Order</div>
    <div class="doc-no">No: <strong>{{ $po->po_code }}</strong></div>

    <table class="info">
        <tr>
            <td class="lbl">Tanggal</td><td class="sep">:</td><td>{{ $tgl($po->po_tanggal) }}</td>
            <td class="lbl">Status</td><td class="sep">:</td><td>{{ ucfirst($po->po_status ?? '-') }}</td>
        </tr>
        <tr>
            <td class="lbl">Supplier</td><td class="sep">:</td><td><strong>{{ $sup?->supplier_nama ?? '-' }}</strong>@if($sup?->supplier_kode) ({{ $sup->supplier_kode }})@endif</td>
            <td class="lbl">Telepon</td><td class="sep">:</td><td>{{ $sup?->supplier_telepon ?? '-' }}</td>
        </tr>
        <tr>
            <td class="lbl">Alamat</td><td class="sep">:</td><td>{{ $sup?->supplier_alamat ?? '-' }}</td>
            <td class="lbl">Kontak</td><td class="sep">:</td><td>{{ $sup?->supplier_kontak_person ?? '-' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:28px;" class="c">No</th>
                <th>Produk</th>
                <th style="width:52px;" class="c">Qty</th>
                <th style="width:96px;" class="r">Harga</th>
                <th style="width:110px;" class="r">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->has_details as $d)
            <tr>
                <td class="c">{{ $loop->iteration }}</td>
                <td>{{ $d->has_product?->product_nama ?? '-' }}@if($d->po_detail_keterangan)<br><span style="font-size:9px;color:#666;">{{ $d->po_detail_keterangan }}</span>@endif</td>
                <td class="c">{{ (int) $d->po_detail_qty }}</td>
                <td class="r">Rp {{ $fmt($d->po_detail_harga) }}</td>
                <td class="r">Rp {{ $fmt((int) $d->po_detail_qty * (float) $d->po_detail_harga) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td></td><td style="width:150px;">Subtotal</td><td style="width:130px;" class="r">Rp {{ $fmt($po->po_subtotal) }}</td></tr>
        @if((float) $po->po_discount > 0)
        <tr><td></td><td>{{ $labelDiskon }}</td><td class="r">- Rp {{ $fmt($po->po_discount_amount) }}</td></tr>
        @endif
        <tr><td></td><td>DPP</td><td class="r">Rp {{ $fmt($po->po_dpp) }}</td></tr>
        @if((float) $po->po_ppn > 0)
        <tr><td></td><td>PPN {{ $pct($po->po_ppn_rate) }}%</td><td class="r">Rp {{ $fmt($po->po_ppn) }}</td></tr>
        @endif
        @if((float) $po->po_pph > 0)
        <tr><td></td><td>PPh {{ $pct($po->po_pph_rate) }}%</td><td class="r">Rp {{ $fmt($po->po_pph) }}</td></tr>
        @endif
        <tr class="grand"><td></td><td>GRAND TOTAL</td><td class="r">Rp {{ $fmt($po->po_grand_total) }}</td></tr>
    </table>

    @if($po->po_keterangan)
    <div class="ket"><strong>Keterangan:</strong> {{ $po->po_keterangan }}</div>
    @endif

    <table class="ttd">
        <tr>
            <td>Dibuat oleh,<br><div class="space"></div>( .................... )</td>
            <td>Disetujui,<br><div class="space"></div>( .................... )</td>
            <td>Supplier,<br><div class="space"></div>( .................... )</td>
        </tr>
    </table>

    <div class="footer">{{ $site['name'] ?? config('app.name') }} &mdash; {{ $po->po_code }} &mdash; {{ now()->format('d/m/Y H:i') }}</div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('Helvetica', 'normal');
            $pdf->page_text(270, 820, "Halaman {PAGE_NUM} dari {PAGE_COUNT}", $font, 8, [0.53, 0.53, 0.53]);
        }
    </script>
</body>
</html>
