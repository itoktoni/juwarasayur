<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Harga Produk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { margin: 16px 14px 16px 14px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1a1a1a; padding: 8px 32px; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; }
        .header h1 { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .meta { font-size: 10px; color: #555; margin-top: 4px; }
        .price-title { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin: 14px 0 6px 0; padding-bottom: 4px; border-bottom: 2px solid #1a1a1a; }
        .category-title { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; margin: 10px 0 4px 2px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #1a1a1a; color: #fff; font-weight: bold; text-align: left; padding: 5px 8px; font-size: 10px; text-transform: uppercase; }
        td { padding: 4px 8px; border-bottom: 1px solid #e0e0e0; font-size: 10.5px; }
        tr:nth-child(even) td { background-color: #f7f7f7; }
        .no-col { width: 32px; text-align: center; }
        .harga-col { width: 120px; text-align: right; white-space: nowrap; }
        .footer { margin-top: 10px; font-size: 9px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 6px; }
        .page-number { font-size: 8px; color: #888; text-align: center; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Harga Produk</h1>
        <div class="meta">
            @if($isAdmin)
                Daftar Harga Customer &amp; Reseller (Grosir)
            @elseif($isReseller)
                Harga Reseller (Grosir) — {{ $user->name }}
            @else
                Harga Pelanggan — {{ $user->name }}
            @endif
            &nbsp;&bull;&nbsp; {{ $date }}
        </div>
    </div>

    @if($isAdmin)
        {{-- ===== 1. HARGA CUSTOMER ===== --}}
        <div class="price-title">Harga Customer</div>
        @forelse($grouped as $group)
            <div class="category-title">{{ $group['name'] }}</div>
            <table>
                <thead>
                    <tr>
                        <th class="no-col">No</th>
                        <th>Produk</th>
                        <th class="harga-col">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['items'] as $index => $item)
                    <tr>
                        <td class="no-col">{{ $index + 1 }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td class="harga-col">Rp {{ number_format($item['harga_normal'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p style="text-align:center; padding:20px; color:#888;">Tidak ada produk aktif.</p>
        @endforelse

        {{-- ===== 2. HARGA RESELLER (GROSIR) — hanya flag is_grosir ===== --}}
        <div class="price-title">Harga Reseller (Grosir)</div>
        @forelse(($groupedGrosir ?? $grouped) as $group)
            <div class="category-title">{{ $group['name'] }}</div>
            <table>
                <thead>
                    <tr>
                        <th class="no-col">No</th>
                        <th>Produk</th>
                        <th class="harga-col">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['items'] as $index => $item)
                    <tr>
                        <td class="no-col">{{ $index + 1 }}</td>
                        <td>{{ $item['nama_grosir'] ?? $item['nama'] }}</td>
                        <td class="harga-col">Rp {{ number_format($item['harga_reseller'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p style="text-align:center; padding:20px; color:#888;">Tidak ada produk aktif.</p>
        @endforelse
    @else
        {{-- Reseller / customer: satu daftar sesuai tipenya, header per kategori --}}
        @forelse($grouped as $group)
            <div class="category-title">{{ $group['name'] }}</div>
            <table>
                <thead>
                    <tr>
                        <th class="no-col">No</th>
                        <th>Produk</th>
                        <th class="harga-col">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['items'] as $index => $item)
                    <tr>
                        <td class="no-col">{{ $index + 1 }}</td>
                        <td>{{ $isReseller ? ($item['nama_grosir'] ?? $item['nama']) : $item['nama'] }}</td>
                        <td class="harga-col">Rp {{ number_format($isReseller ? $item['harga_reseller'] : $item['harga_normal'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @empty
            <p style="text-align:center; padding:20px; color:#888;">Tidak ada produk aktif.</p>
        @endforelse
    @endif

    <div class="footer">
        {{ config('app.name', 'Mayur') }} &mdash; {{ $date }}
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont('Helvetica', 'normal');
            $size = 8;
            $color = [0.53, 0.53, 0.53];
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            // center bottom — 595pt = A4 portrait width, 15pt dari bawah
            $pdf->page_text(250, 820, $text, $font, $size, $color);
        }
    </script>
</body>
</html>
