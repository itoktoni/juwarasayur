@php
    use Illuminate\Support\Carbon;

    $site = \App\Models\WebsiteSetting::merged();
    $logoUrl = !empty($site['logo']) ? \App\Models\WebsiteSetting::fileUrl($site['logo']) : null;
    $fmtDate = fn ($v) => $v ? Carbon::parse($v)->locale('id')->translatedFormat('d F Y') : '-';
    $fmtRp = fn ($v) => 'Rp '.number_format((float) $v, 0, ',', '.');
    $fmtQty = fn ($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') ?: '0';
    $pct = fn ($v) => rtrim(rtrim((string) $v, '0'), '.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Prepare ({{ $list->count() }} SO)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { background: #fff; }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.55;
        }
        .sheet { width: 190mm; margin: 0 auto; padding: 4mm 0 6mm; }
        .sheet + .sheet { page-break-before: always; }
        .accent-bar { height: 1.2mm; border-radius: 1mm; background: linear-gradient(90deg, #1e3a8a, #60a5fa); }
        .head { display: flex; justify-content: space-between; align-items: center; gap: 3mm; margin-top: 1.5mm; }
        .brand { display: flex; gap: 2mm; align-items: center; min-width: 0; }
        .brand img.logo { width: 12mm; height: 12mm; object-fit: contain; border-radius: 1.5mm; background: #eff6ff; padding: 0.8mm; flex-shrink: 0; }
        .brand .logo-fallback {
            width: 12mm; height: 12mm; display: flex; align-items: center; justify-content: center;
            border-radius: 1.5mm; background: #eff6ff; color: #1e40af;
            font-weight: 800; font-size: 8px; text-align: center; padding: 0.8mm; flex-shrink: 0;
        }
        .brand h1 { font-size: 14px; letter-spacing: 0.5px; color: #1e3a8a; line-height: 1.2; }
        .brand .addr { color: #6b7280; font-size: 9px; line-height: 1.35; }
        .doc-badge { text-align: right; color: #111; white-space: nowrap; }
        .doc-badge h2 { font-size: 14px; letter-spacing: 2px; line-height: 1.2; }
        .doc-badge span { font-size: 8px; letter-spacing: 2.5px; color: #374151; }
        .doc-badge .no { margin-top: 0.5mm; font-size: 10.5px; font-weight: bold; }
        .meta { display: flex; gap: 3mm; margin-top: 3mm; align-items: stretch; }
        .meta > div { flex: 1; min-width: 0; }
        .card { border: 0.6mm solid #111; border-radius: 2mm; padding: 2mm 3mm; height: 100%; }
        .card h3 { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; color: #1d4ed8; margin-bottom: 1mm; }
        .card .row { display: flex; gap: 2mm; margin-top: 0.5mm; }
        .card .row .k { width: 20mm; flex-shrink: 0; color: #6b7280; }
        .card .row .v { color: #111827; min-width: 0; overflow-wrap: break-word; }
        table.items { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 3mm; border-radius: 2mm; overflow: hidden; border: 1px solid #e2e8f0; }
        table.items th { background: #1e3a8a; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 1.8mm 2.5mm; text-align: left; }
        table.items th.c, table.items td.c { text-align: center; }
        table.items th.r, table.items td.r { text-align: right; white-space: nowrap; }
        table.items td { padding: 1.5mm 2.5mm; border-top: 1px solid #eef2f7; vertical-align: top; }
        table.items tbody tr:nth-child(even) td { background: #f8fafc; }
        table.items tr.grand td { font-weight: 800; font-size: 13px; background: #eff6ff; }
        .pay { margin-top: 3mm; font-size: 10.5px; color: #374151; }
        .sign { display: flex; gap: 6mm; margin-top: 6mm; text-align: center; color: #374151; font-size: 11px; }
        .sign > div { flex: 1; }
        .sign .sp { height: 14mm; }
        .sign .ln { border-top: 1.5px solid #9ca3af; padding-top: 1mm; color: #6b7280; }
        .toolbar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
            display: flex; gap: 8px; justify-content: center; align-items: center;
            padding: 12px; background: #fff; border-top: 1px solid #d1d5db;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.08); font-size: 14px;
        }
        body { padding-bottom: 72px; }
        .toolbar button { padding: 8px 18px; border: 0; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-back { background: #e5e7eb; color: #374151; text-decoration: none; display: inline-flex; align-items: center; padding: 8px 18px; border-radius: 8px; font-weight: 700; }
        @page { size: A4 portrait; margin: 10mm; }
        @media print {
            .toolbar { display: none; }
            body { padding-bottom: 0; }
            .sheet { width: auto; padding: 0; }
            table.items th, table.items tr.grand td, .accent-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.items tbody tr:nth-child(even) td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">Print ({{ $list->count() }} Invoice)</button>
        <a class="btn-back" href="{{ route('so-so.getTable') }}">Kembali</a>
    </div>

    @foreach($list as $so)
        @php
            $customerName = $so->so_customer_name ?: ($so->has_customer?->name ?? '-');
            $customerPhone = $so->has_customer?->phone ?? '-';
            // Baris tagihan = qty PREPARE x harga (bukan qty order)
            $lines = $so->has_details->map(fn ($d) => [
                'nama' => $d->has_product?->product_nama ?? '-',
                'ket' => $d->so_detail_keterangan,
                'qty' => (int) $d->has_prepare_allocations->sum('qty'),
                'harga' => (float) $d->so_detail_harga,
            ]);
            $subPrepare = $lines->sum(fn ($l) => $l['qty'] * $l['harga']);
            $diskonAmount = max(0, (float) $so->so_subtotal - (float) $so->so_dpp);
            $grandPrepare = max(0, $subPrepare - $diskonAmount) + (float) $so->so_ppn + (float) $so->so_pph + (float) $so->so_shipping_fee;
        @endphp
        <div class="sheet">
            <div class="accent-bar"></div>

            <div class="head">
                <div class="brand">
                    @if($logoUrl)
                        <img class="logo" src="{{ $logoUrl }}" alt="Logo">
                    @else
                        <div class="logo-fallback">{{ $site['name'] ?? config('app.name') }}</div>
                    @endif
                    <div>
                        <h1>{{ strtoupper($site['name'] ?? config('app.name')) }}</h1>
                        @if(!empty($site['alamat']))<div class="addr">{{ $site['alamat'] }}</div>@endif
                        <div class="addr">
                            @if(!empty($site['telepon']))<span>Telp: {{ $site['telepon'] }}</span>@endif
                            @if(!empty($site['telepon']) && !empty($site['email']))<span> &bull; </span>@endif
                            @if(!empty($site['email']))<span>{{ $site['email'] }}</span>@endif
                        </div>
                    </div>
                </div>
                <div class="doc-badge">
                    <h2>INVOICE</h2>
                    <span>TAGIHAN (QTY PREPARE)</span>
                    <div class="no">{{ $so->so_code }}</div>
                </div>
            </div>

            <div class="meta">
                <div>
                    <div class="card">
                        <h3>Ditagihkan Kepada</h3>
                        <div class="row"><span class="k">Customer</span><span class="v"><strong>{{ $customerName }}</strong></span></div>
                        <div class="row"><span class="k">No. Telp</span><span class="v">{{ $customerPhone }}</span></div>
                    </div>
                </div>
                <div>
                    <div class="card">
                        <h3>Detail Invoice</h3>
                        <div class="row"><span class="k">Tanggal</span><span class="v">{{ $fmtDate($so->so_tanggal) }}</span></div>
                        <div class="row"><span class="k">No. PO / Ref</span><span class="v"><strong>{{ $so->so_po_ref ?: $so->so_code }}</strong></span></div>
                    </div>
                </div>
            </div>

            <table class="items">
                <thead>
                    <tr>
                        <th class="c" style="width:10mm;">No</th>
                        <th>Nama Barang / Deskripsi</th>
                        <th class="c" style="width:16mm;">Qty</th>
                        <th class="r" style="width:28mm;">Harga</th>
                        <th class="r" style="width:32mm;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lines as $l)
                        <tr>
                            <td class="c">{{ $loop->iteration }}</td>
                            <td><strong>{{ $l['nama'] }}</strong>@if($l['ket']) <span style="font-size:10px;color:#6b7280;">({{ $l['ket'] }})</span>@endif</td>
                            <td class="c">{{ $fmtQty($l['qty']) }}</td>
                            <td class="r">{{ $fmtRp($l['harga']) }}</td>
                            <td class="r">{{ $fmtRp($l['qty'] * $l['harga']) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="c">Subtotal (qty prepare)</td>
                        <td class="r">{{ $fmtRp($subPrepare) }}</td>
                    </tr>
                    @if($diskonAmount > 0)
                        <tr>
                            <td colspan="4" class="c">Diskon{{ $so->so_discount_type === 'percent' ? ' '.$pct($so->so_discount).'%' : '' }}</td>
                            <td class="r">-{{ $fmtRp($diskonAmount) }}</td>
                        </tr>
                    @endif
                    @if((float) $so->so_ppn > 0)
                        <tr>
                            <td colspan="4" class="c">PPN {{ $pct($so->so_ppn_rate) }}%</td>
                            <td class="r">{{ $fmtRp($so->so_ppn) }}</td>
                        </tr>
                    @endif
                    @if((float) $so->so_pph > 0)
                        <tr>
                            <td colspan="4" class="c">PPh {{ $pct($so->so_pph_rate) }}%</td>
                            <td class="r">{{ $fmtRp($so->so_pph) }}</td>
                        </tr>
                    @endif
                    @if((float) $so->so_shipping_fee > 0)
                        <tr>
                            <td colspan="4" class="c">Ongkir</td>
                            <td class="r">{{ $fmtRp($so->so_shipping_fee) }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td colspan="4" class="c">Total Tagihan</td>
                        <td class="r">{{ $fmtRp($grandPrepare) }}</td>
                    </tr>
                </tbody>
            </table>

            @if(!empty($site['bank_name']) || !empty($site['bank_account_no']))
                <div class="pay">
                    Pembayaran: <strong>{{ $site['bank_name'] ?? '' }} {{ $site['bank_account_no'] ?? '' }}</strong>
                    @if(!empty($site['bank_account_name'])) a.n. {{ $site['bank_account_name'] }}@endif
                </div>
            @endif

            <div class="sign">
                <div>
                    <div>Penerima,</div>
                    <div class="sp"></div>
                    <div class="ln">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                </div>
                <div>
                    <div>Hormat Kami,</div>
                    <div class="sp"></div>
                    <div class="ln">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                </div>
            </div>
        </div>
    @endforeach

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
