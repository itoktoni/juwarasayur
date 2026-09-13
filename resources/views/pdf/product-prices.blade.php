<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Harga Produk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #1a1a1a; padding: 24px; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #1a1a1a; padding-bottom: 12px; }
        .header h1 { font-size: 17px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .meta { font-size: 11px; color: #555; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 0; }
        th { background-color: #1a1a1a; color: #fff; font-weight: bold; text-align: left; padding: 7px 10px; font-size: 11px; text-transform: uppercase; }
        td { padding: 6px 10px; border-bottom: 1px solid #e0e0e0; }
        tr:nth-child(even) td { background-color: #f7f7f7; }
        .no-col { width: 36px; text-align: center; }
        .harga-col { width: 130px; text-align: right; white-space: nowrap; }
        .footer { margin-top: 20px; font-size: 10px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }

        /* Group header — Hub-and-Spoke + Cross-Dock manifest */
        .group-wrap { margin-top: 14px; page-break-inside: avoid; }
        .group-wrap:first-of-type { margin-top: 10px; }
        .category-bar {
            background-color: transparent;
            color: #1a1a1a;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 0 4px 0;
            border-bottom: 2px solid #1a1a1a;
            margin-bottom: 0;
        }
        .category-bar small {
            font-weight: normal;
            text-transform: none;
            letter-spacing: 0;
            opacity: 0.85;
            font-size: 10px;
        }
        .category-bar .badge {
            display: inline-block;
            background: #4a5568;
            color: #fff;
            border-radius: 3px;
            padding: 1px 6px;
            font-size: 9px;
            margin-left: 8px;
            vertical-align: middle;
        }
        .group-table { margin-top: 0; }
        .group-table th { background-color: #4a5568; font-size: 10px; }
        .group-table td { font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Harga Produk</h1>
        <div class="meta">
            @if($isAdmin)
                Daftar Harga Lengkap (Admin)
            @elseif($isReseller)
                Harga Reseller — {{ $user->name }}
            @else
                Harga Pelanggan — {{ $user->name }}
            @endif
            &nbsp;&bull;&nbsp; {{ $date }}
        </div>
    </div>

    @if(isset($grouped) && $grouped->isNotEmpty())
        @foreach($grouped as $group)
        <div class="group-wrap">
            <div class="category-bar">
                {{ $group['name'] }}
            </div>
            <table class="group-table">
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
                        <td class="harga-col">Rp {{ number_format($isReseller ? $item['harga_reseller'] : $item['harga_normal'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    @else
        {{-- Fallback flat table jika grouped kosong (backward compat) --}}
        <table>
            <thead>
                <tr>
                    <th class="no-col">No</th>
                    <th>Produk</th>
                    <th class="harga-col">Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                <tr>
                    <td class="no-col">{{ $index + 1 }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td class="harga-col">Rp {{ number_format($isReseller ? $item['harga_reseller'] : $item['harga_normal'], 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center; padding:20px; color:#888;">Tidak ada produk aktif.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        {{ config('app.name', 'Mayur') }} &mdash; {{ $date }}
    </div>
</body>
</html>
