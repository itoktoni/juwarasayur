@php
    use Illuminate\Support\Carbon;

    $site = \App\Models\WebsiteSetting::merged();
    $logoUrl = !empty($site['logo']) ? \App\Models\WebsiteSetting::fileUrl($site['logo']) : null;
    // No. Surat Jalan: DO/YYYY/MM/NNN (N = id SO, pad 3 digit)
    $doNumber = fn ($so) => 'DO/'.Carbon::parse($so->so_tanggal)->format('Y/m').'/'.str_pad((string) $so->id, 3, '0', STR_PAD_LEFT);
    $fmtDate = fn ($v) => $v ? Carbon::parse($v)->locale('id')->translatedFormat('d F Y') : '-';
    // Qty ala contoh: 4 / 1.5 / 250 (desimal titik, tanpa trailing zero)
    $fmtQty = fn ($v) => rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.') ?: '0';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Surat Jalan ({{ $list->count() }} SO)</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { background: #fff; }
        body {
            font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.55;
        }
        .sheet {
            width: 190mm;
            margin: 0 auto;
            padding: 4mm 0 6mm;
        }
        /* ponytail: page-break hanya ANTAR lembar (:last-child tak cocok karena
           ada <script> setelahnya) — ini yang bikin selalu 2 halaman. */
        .sheet + .sheet { page-break-before: always; }

        /* Pita aksen atas */
        .accent-bar { height: 1.2mm; border-radius: 1mm; background: linear-gradient(90deg, #166534, #4ade80); }

        .head { display: flex; justify-content: space-between; align-items: center; gap: 3mm; margin-top: 1.5mm; }
        .brand { display: flex; gap: 2mm; align-items: center; min-width: 0; }
        .brand img.logo {
            width: 12mm; height: 12mm; object-fit: contain;
            border-radius: 1.5mm; background: #f0fdf4; padding: 0.8mm; flex-shrink: 0;
        }
        .brand .logo-fallback {
            width: 12mm; height: 12mm; display: flex; align-items: center; justify-content: center;
            border-radius: 1.5mm; background: #f0fdf4; color: #166534;
            font-weight: 800; font-size: 8px; text-align: center; padding: 0.8mm; flex-shrink: 0;
        }
        .brand h1 { font-size: 14px; letter-spacing: 0.5px; color: #14532d; line-height: 1.2; }
        .brand .addr { color: #6b7280; font-size: 9px; line-height: 1.35; }
        .doc-badge { text-align: right; color: #111; white-space: nowrap; }
        .doc-badge h2 { font-size: 14px; letter-spacing: 2px; line-height: 1.2; }
        .doc-badge span { font-size: 8px; letter-spacing: 2.5px; color: #374151; }
        .doc-badge .no { margin-top: 0.5mm; font-size: 10.5px; font-weight: bold; }

        .meta { display: flex; gap: 3mm; margin-top: 3mm; align-items: stretch; }
        .meta > div { flex: 1; min-width: 0; }
        .card {
            border: 0.6mm solid #111;
            border-radius: 2mm; padding: 2mm 3mm; height: 100%;
        }
        .card h3 { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; color: #16a34a; margin-bottom: 1mm; }
        .card .name { font-size: 12px; font-weight: 800; color: #111827; overflow-wrap: break-word; }
        .card .row { display: flex; gap: 2mm; margin-top: 0.5mm; }
        .card .row .k { width: 20mm; flex-shrink: 0; color: #6b7280; }
        .card .row .v { color: #111827; min-width: 0; overflow-wrap: break-word; }
        .addr-full { margin-top: 3mm; }

        table.items { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 3mm; border-radius: 2mm; overflow: hidden; border: 1px solid #e2e8f0; }
        table.items th { background: #14532d; color: #fff; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; padding: 1.8mm 2.5mm; text-align: left; }
        table.items th.c, table.items td.c { text-align: center; }
        table.items td { padding: 1.5mm 2.5mm; border-top: 1px solid #eef2f7; vertical-align: top; }
        table.items tbody tr:nth-child(even) td { background: #f8fafc; }
        table.items td.qty { font-weight: 800; font-size: 12px; }

        .catatan {
            margin-top: 3mm; background: #fffbeb; border: 1px dashed #d97706;
            border-radius: 2mm; padding: 2mm 3mm; font-size: 11px;
        }
        .catatan p { font-weight: 800; color: #92400e; font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 0.5mm; }

        .sign { display: flex; gap: 6mm; margin-top: 6mm; text-align: center; color: #374151; font-size: 11px; }
        .sign > div { flex: 1; }
        .sign .sp { height: 14mm; }
        .sign .ln { border-top: 1.5px solid #9ca3af; padding-top: 1mm; color: #6b7280; }

        .toolbar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
            display: flex; gap: 8px; justify-content: center; align-items: center;
            padding: 12px; background: #fff; border-top: 1px solid #d1d5db;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.08);
            font-size: 14px;
        }
        body { padding-bottom: 72px; }
        .toolbar button {
            padding: 8px 18px; border: 0; border-radius: 8px; cursor: pointer;
            font-weight: 700; font-size: 14px;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-back { background: #e5e7eb; color: #374151; text-decoration: none; display: inline-flex; align-items: center; padding: 8px 18px; border-radius: 8px; font-weight: 700; }

        @page { size: A4 portrait; margin: 10mm; }

        @media print {
            .toolbar { display: none; }
            body { padding-bottom: 0; }
            .sheet { width: auto; padding: 0; }
            table.items th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .doc-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .accent-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.items tbody tr:nth-child(even) td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">Print ({{ $list->count() }} DO)</button>
        <a class="btn-back" href="{{ route('so-so.getTable') }}">Kembali</a>
    </div>

    @foreach($list as $so)
        @php
            $customerName = $so->so_customer_name ?: ($so->has_customer?->name ?? '-');
            $customerPhone = $so->has_customer?->phone ?? '-';
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
                    <h2>SURAT JALAN</h2>
                    <span>DELIVERY ORDER</span>
                    <div><span class="no">{{ $doNumber($so) }}</span></div>
                </div>
            </div>

            <div class="meta">
                <div>
                    <div class="card">
                        <h3>Kepada Yth.</h3>
                        <div class="row"><span class="k">Customer</span><span class="v name">{{ $customerName }}</span></div>
                        <div class="row"><span class="k">No. Telp</span><span class="v">{{ $customerPhone }}</span></div>
                    </div>
                </div>
                <div>
                    <div class="card">
                        <h3>Detail Pengiriman</h3>
                        <div class="row"><span class="k">Tanggal</span><span class="v">{{ $fmtDate($so->so_tanggal) }}</span></div>
                        @if($so->so_shipping_method === 'delivery')
                            <div class="row"><span class="k">No. PO / Ref</span><span class="v"><strong>{{ $so->so_po_ref ?: $so->so_code }}</strong></span></div>
                        @else
                            <div class="row"><span class="k">Metode</span><span class="v"><strong>{{ \Modules\So\Enums\ShippingMethodEnum::getDescription($so->so_shipping_method) }}{{ $so->so_cod_location ? ' ('.$so->so_cod_location.')' : '' }}</strong></span></div>
                        @endif
                    </div>
                </div>
            </div>

            @if($so->so_shipping_method === 'delivery')
            <div class="card addr-full">
                <h3>Alamat Pengiriman</h3>
                <div class="v">{{ $so->so_address ?: '-' }}</div>
            </div>
            @endif

            <table class="items">
                <thead>
                    <tr>
                        <th class="c" style="width:10mm;">No</th>
                        <th>Nama Barang / Deskripsi</th>
                        <th class="c" style="width:18mm;">Qty</th>
                        <th style="width:46mm;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($so->has_details as $d)
                        <tr>
                            <td class="c">{{ $loop->iteration }}</td>
                            <td><strong>{{ $d->has_product?->product_nama ?? '-' }}</strong></td>
                            <td class="c qty">{{ $fmtQty($d->so_detail_qty) }}</td>
                            <td>{{ $d->so_detail_keterangan ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="catatan">
                <p>Catatan</p>
                <div>{{ $so->so_keterangan ?: 'Barang telah diterima dalam kondisi baik dan cukup.' }}</div>
            </div>

            <div class="sign">
                <div>
                    <div>Tanda Tangan Penerima</div>
                    <div class="sp"></div>
                    <div class="ln">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                </div>
                <div>
                    <div>Pengemudi / Kurir</div>
                    <div class="sp"></div>
                    <div class="ln">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                </div>
                <div>
                    <div>Hormat Kami / Pengirim</div>
                    <div class="sp"></div>
                    <div class="ln">( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</div>
                </div>
            </div>
        </div>
    @endforeach

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
