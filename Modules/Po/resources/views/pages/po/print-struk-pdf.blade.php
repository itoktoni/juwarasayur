@php
    $fmt = fn ($v) => number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
    $tgl = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : '-';
    $sup = $po->has_supplier;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk {{ $po->po_code }}</title>
<style>
  * { margin:0; padding:0; }
  @page { margin:8px 4px; }
  body { font-family:'DejaVu Sans Mono','Courier New',monospace; font-size:9px; color:#000; }
</style>
</head>
<body>

<!-- OUTER WRAPPER: padding kiri-kanan 6pt -->
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td style="padding:0 6pt;">

  <!-- ========== HEADER ========== -->
  <table width="100%" cellpadding="0" cellspacing="0">
  <tr>
  <td align="center" style="padding:0 0 6pt 0;">
    <div style="font-size:14px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">{{ $site['name'] ?? config('app.name') }}</div>
    @if(!empty($site['alamat']))
    <div style="font-size:7px; color:#444; margin-top:2pt;">{{ $site['alamat'] }}</div>
    @endif
    @if(!empty($site['telepon']))
    <div style="font-size:7px; color:#444; margin-top:1pt;">Telp: {{ $site['telepon'] }}</div>
    @endif
    <div style="font-size:10px; font-weight:bold; margin-top:4pt;">** PURCHASE ORDER **</div>
  </td>
  </tr>
  </table>

  <!-- DIVIDER -->
  <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px dashed #000; font-size:1px; line-height:1px;">&nbsp;</td></tr></table>

  <!-- ========== INFO ========== -->
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td width="24" style="padding:3pt 4pt 3pt 0; color:#555;">No</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ $po->po_code }}</td>
    </tr>
    <tr>
      <td style="padding:3pt 4pt 3pt 0; color:#555;">Tgl</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ $tgl($po->po_tanggal) }}</td>
    </tr>
    <tr>
      <td style="padding:3pt 4pt 3pt 0; color:#555;">Supl</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ $sup?->supplier_nama ?? '-' }}</td>
    </tr>
    @if($po->po_keterangan)
    <tr>
      <td style="padding:3pt 4pt 3pt 0; color:#555;">Ket</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ \Illuminate\Support\Str::limit($po->po_keterangan, 40) }}</td>
    </tr>
    @endif
  </table>

  <!-- DIVIDER -->
  <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px dashed #000; font-size:1px; line-height:1px;">&nbsp;</td></tr></table>

  <!-- ========== ITEMS ========== -->
  <table width="100%" cellpadding="0" cellspacing="0">
    @foreach($po->has_details as $d)
    <tr>
      <td colspan="2" style="padding:4pt 4pt 1pt 0; font-size:9px;">{{ $loop->iteration }}. {{ $d->has_product?->product_nama ?? '-' }}@if($d->po_detail_keterangan) <span style="font-style:italic; color:#555;">({{ \Illuminate\Support\Str::limit($d->po_detail_keterangan, 15) }})</span>@endif</td>
    </tr>
    <tr>
      <td style="padding:0 4pt 4pt 12pt; font-size:8px; color:#444;">{{ (int) $d->po_detail_qty }} x {{ $fmt($d->po_detail_harga) }}</td>
      <td width="60" align="right" style="padding:0 0 4pt 0; font-size:9px;">{{ $fmt((int) $d->po_detail_qty * (float) $d->po_detail_harga) }}</td>
    </tr>
    @endforeach
  </table>

  <!-- DIVIDER -->
  <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px dashed #000; font-size:1px; line-height:1px;">&nbsp;</td></tr></table>

  <!-- ========== SUMMARY ========== -->
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">Subtotal</td>
      <td width="65" align="right" style="padding:3pt 0;">{{ $fmt($po->po_subtotal) }}</td>
    </tr>
    @if((float) $po->po_discount > 0)
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">{{ $po->po_discount_type === 'percent' ? 'Diskon '.$pct($po->po_discount).'%' : 'Diskon' }}</td>
      <td width="65" align="right" style="padding:3pt 0;">-{{ $fmt($po->po_discount_amount) }}</td>
    </tr>
    @endif
    @if((float) $po->po_ppn > 0)
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">PPN {{ $pct($po->po_ppn_rate) }}%</td>
      <td width="65" align="right" style="padding:3pt 0;">{{ $fmt($po->po_ppn) }}</td>
    </tr>
    @endif
    @if((float) $po->po_pph > 0)
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">PPh {{ $pct($po->po_pph_rate) }}%</td>
      <td width="65" align="right" style="padding:3pt 0;">{{ $fmt($po->po_pph) }}</td>
    </tr>
    @endif
    <tr>
      <td style="padding:4pt 4pt 0 0; font-size:11px; font-weight:bold; border-top:1px dashed #000;">TOTAL</td>
      <td width="65" align="right" style="padding:4pt 0 0 0; font-size:11px; font-weight:bold; border-top:1px dashed #000;">Rp {{ $fmt($po->po_grand_total) }}</td>
    </tr>
  </table>

  <!-- FOOTER -->
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding:8pt 0 0 0; font-size:8px;">~~ Terima kasih ~~</td></tr>
    <tr><td align="center" style="padding:2pt 0 0 0; font-size:7px; color:#666;">{{ $po->po_code }} &mdash; {{ now()->format('d/m/Y H:i') }}</td></tr>
  </table>

</td>
</tr>
</table>

</body>
</html>
