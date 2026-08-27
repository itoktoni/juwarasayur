@php
    $site = \App\Models\WebsiteSetting::merged();
    $paper = (int) config('printer.web.paper_width', 80) === 58 ? 58 : 80;
    $contentW = $paper === 58 ? 52 : 72;
    $cutW = $paper === 58 ? 56 : 76;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Print Label Prepare SO{{ $so ? ' - ' . $so->so_code : '' }}</title>
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
        .label {
            width: {{ $contentW }}mm;
            padding: 2mm 1mm;
            border: 1px dashed #000;
        }
        .label + .label { margin-top: 4mm; }
        .label .center { text-align: center; }
        .label .product { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 1mm; }
        .label .qty { font-size: 22px; font-weight: bold; text-align: center; margin: 1mm 0; padding: 1mm 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; }
        .label .rows div { display: flex; justify-content: space-between; gap: 2mm; }
        .label .footer { text-align: center; margin-top: 2mm; font-size: 10px; }

        .cut-line {
            width: {{ $cutW }}mm;
            text-align: center;
            padding: 1mm 0;
            white-space: nowrap;
            overflow: hidden;
            color: #000;
            letter-spacing: 1px;
            user-select: none;
        }

        .toolbar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 50;
            display: flex; gap: 8px; justify-content: center; align-items: center;
            padding: 12px;
            background: #fff; border-top: 1px solid #d1d5db;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.08);
            font-family: system-ui, sans-serif; font-size: 14px;
        }
        body { padding-bottom: 72px; }
        .toolbar button, .toolbar a {
            padding: 8px 18px; border: 0; border-radius: 8px; cursor: pointer;
            font-weight: 700; font-size: 14px; text-decoration: none;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-back { background: #e5e7eb; color: #374151; }

        @page { size: {{ $paper }}mm auto; margin: 0; }
        @media print {
            .toolbar { display: none; }
            body { padding-bottom: 0; }
            .label { border: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">🖨️ Print ({{ $allocations->count() }} Label)</button>
        <a class="btn-back" href="{{ route('prepare.progress', ['so_id' => $so?->id]) }}">← Kembali</a>
    </div>

    @if(!$so)
        <div class="label">
            <p class="center">Pilih SO di halaman sebelumnya untuk generate label.</p>
        </div>
    @else
        @foreach($allocations as $alloc)
            @if(!$loop->first)
                <div class="cut-line">✂ - - - - - - - - - - - - - - - - - - - - - - - - - - - - ✂</div>
            @endif
            <div class="label">
                <div class="center">
                    <div style="font-size: 9px; text-transform: uppercase;">{{ $site['name'] ?? config('app.name') }}</div>
                </div>
                <div class="product">{{ $alloc->has_product?->product_nama ?? '-' }}</div>
                <div class="qty">{{ $alloc->qty }} {{ $alloc->has_product?->has_satuan?->satuan_nama ?? 'pcs' }}</div>
                <div class="rows">
                    <div><span>SO</span><span>{{ $alloc->has_so_detail?->has_so?->so_code ?? '-' }}</span></div>
                    <div><span>Customer</span><span>{{ \Illuminate\Support\Str::limit($alloc->has_so_detail?->has_so?->so_customer_name ?? '-', 22) }}</span></div>
                    <div><span>Lokasi</span><span>{{ $alloc->has_lokasi?->lokasi_nama ?? '-' }}</span></div>
                    @if($alloc->expired_date)
                        <div><span>Exp</span><span>{{ \Illuminate\Support\Carbon::parse($alloc->expired_date)->format('d/m/Y') }}</span></div>
                    @endif
                    <div><span>Siap</span><span>{{ \Illuminate\Support\Carbon::parse($alloc->prepared_at)->format('d/m/Y H:i') }}</span></div>
                </div>
                <div class="footer">{{ $alloc->has_product?->product_kode ?? '' }}</div>
            </div>
        @endforeach

        @if($allocations->isEmpty())
            <div class="label">
                <p class="center">SO ini belum ada barang yang disiapkan.</p>
            </div>
        @endif
    @endif

    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
</body>
</html>
