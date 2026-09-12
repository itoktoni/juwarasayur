<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Struk {{ $so->so_code }}</title>
<style>
  * { margin:0; padding:0; }
  body { font-family: 'DejaVu Sans Mono', 'Courier New', monospace; font-size:9px; color:#000; }
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
    <div style="font-size:14px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">{{ $site['name'] ?? config('app.name','Juwara Sayur') }}</div>
    @if(!empty($site['alamat']))
    <div style="font-size:7px; color:#444; margin-top:2pt;">{{ $site['alamat'] }}</div>
    @endif
    @if(!empty($site['telepon']))
    <div style="font-size:7px; color:#444; margin-top:1pt;">Telp: {{ $site['telepon'] }}</div>
    @endif
  </td>
  </tr>
  </table>

  <!-- DIVIDER -->
  <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px dashed #000; font-size:1px; line-height:1px;">&nbsp;</td></tr></table>

  <!-- ========== INFO ========== -->
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td width="16" style="padding:3pt 4pt 3pt 0; color:#555;">No</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ $so->so_code }}</td>
    </tr>
    <tr>
      <td style="padding:3pt 4pt 3pt 0; color:#555;">Tgl</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ formatDate($so->so_tanggal, 'd/m/Y H:i') }}</td>
    </tr>
    <tr>
      <td style="padding:3pt 4pt 3pt 0; color:#555;">Cust</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ $so->so_customer_name ?: ($so->has_customer?->name ?? '-') }}{{ $so->so_customer_phone ? ' ('.$so->so_customer_phone.')' : '' }}</td>
    </tr>
    @if($so->so_address)
    <tr>
      <td style="padding:3pt 4pt 3pt 0; color:#555;">Almt</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ \Illuminate\Support\Str::limit($so->so_address, 35) }}</td>
    </tr>
    @endif
    <tr>
      <td style="padding:3pt 4pt 3pt 0; color:#555;">Stat</td>
      <td style="padding:3pt 0; font-weight:bold;">: {{ \Modules\So\Enums\SoStatusEnum::getDescription($so->so_status) }}</td>
    </tr>
  </table>

  <!-- DIVIDER -->
  <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px dashed #000; font-size:1px; line-height:1px;">&nbsp;</td></tr></table>

  <!-- ========== ITEMS ========== -->
  <table width="100%" cellpadding="0" cellspacing="0">
    @foreach($so->has_details as $d)
    <tr>
      <td colspan="2" style="padding:4pt 4pt 1pt 0; font-size:9px;">{{ $loop->iteration }}. {{ $d->has_product?->product_nama ?? '-' }}@if($d->so_detail_keterangan) <span style="font-style:italic; color:#555;">({{ \Illuminate\Support\Str::limit($d->so_detail_keterangan, 15) }})</span>@endif</td>
    </tr>
    <tr>
      <td style="padding:0 4pt 4pt 12pt; font-size:8px; color:#444;">{{ $d->so_detail_qty }} x {{ number_format((float)$d->so_detail_harga,0,',','.') }}</td>
      <td width="55" align="right" style="padding:0 0 4pt 0; font-size:9px;">{{ number_format((int)$d->so_detail_qty * (float)$d->so_detail_harga,0,',','.') }}</td>
    </tr>
    @endforeach
  </table>

  <!-- DIVIDER -->
  <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px dashed #000; font-size:1px; line-height:1px;">&nbsp;</td></tr></table>

  <!-- ========== SUMMARY ========== -->
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">Subtotal</td>
      <td width="60" align="right" style="padding:3pt 0;">{{ number_format((float)$so->so_subtotal,0,',','.') }}</td>
    </tr>
    @if((float)$so->so_discount > 0)
      @php
        $diskonAmount = max(0, (float)$so->so_subtotal - (float)$so->so_dpp);
        $labelDiskon = $so->so_discount_type === 'percent' ? 'Diskon '.$so->so_discount.'%' : 'Diskon';
      @endphp
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">{{ $labelDiskon }}</td>
      <td align="right" style="padding:3pt 0;">-{{ number_format($diskonAmount,0,',','.') }}</td>
    </tr>
    @endif
    @if((float)$so->so_ppn > 0)
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">PPN {{ rtrim(rtrim((string)$so->so_ppn_rate,'0'),'.') }}%</td>
      <td align="right" style="padding:3pt 0;">{{ number_format((float)$so->so_ppn,0,',','.') }}</td>
    </tr>
    @endif
    @if((float)$so->so_pph > 0)
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">PPh {{ rtrim(rtrim((string)$so->so_pph_rate,'0'),'.') }}%</td>
      <td align="right" style="padding:3pt 0;">{{ number_format((float)$so->so_pph,0,',','.') }}</td>
    </tr>
    @endif
    @if((float)$so->so_shipping_fee > 0)
    <tr>
      <td style="padding:3pt 4pt 3pt 0;">Ongkir</td>
      <td align="right" style="padding:3pt 0;">{{ number_format((float)$so->so_shipping_fee,0,',','.') }}</td>
    </tr>
    @endif
    <tr>
      <td style="border-top:1px dashed #000; border-bottom:1.5px solid #000; padding:6pt 4pt; font-size:11px; font-weight:bold;">TOTAL</td>
      <td width="60" align="right" style="border-top:1px dashed #000; border-bottom:1.5px solid #000; padding:6pt 0; font-size:11px; font-weight:bold;">Rp {{ number_format((float)$so->so_grand_total,0,',','.') }}</td>
    </tr>
    <tr>
      <td style="padding:3pt 4pt 0 0; font-size:7.5px; color:#555;">Bayar (unik)</td>
      <td align="right" style="padding:3pt 0 0 0; font-size:7.5px; color:#555;">Rp {{ number_format((float)$so->so_unique_amount,0,',','.') }}</td>
    </tr>
  </table>

  <!-- ========== QR ========== -->
  @if($qrDataUri)
  <table width="100%" cellpadding="0" cellspacing="0">
  <tr><td align="center" style="padding:8pt 0 5pt 0;">
    <img src="{{ $qrDataUri }}" alt="QRIS" width="110" height="110" style="border:1px solid #ddd; padding:3pt; background:#fff;">
    <div style="font-size:7px; color:#555; margin-top:3pt;">Scan QRIS untuk bayar</div>
  </td></tr>
  </table>
  @endif

  <!-- ========== LINK BOX ========== -->
  <table width="100%" cellpadding="5" cellspacing="0" style="border:1px dashed #999; background:#f5f5f5;">
    <tr>
      <td align="center" style="font-size:6.5px; color:#333; word-break:break-all; line-height:1.5;">
        {{ $paymentLink }}<br>
        Exp {{ $expiryMinutes }} mnt &middot; Dibuat {{ $so->created_at?->format('d/m H:i') }}
      </td>
    </tr>
  </table>

  <!-- DIVIDER -->
  <table width="100%" cellpadding="0" cellspacing="0"><tr><td style="border-top:1px dashed #000; font-size:1px; line-height:1px; padding-top:4pt;">&nbsp;</td></tr></table>

  <!-- ========== FOOTER ========== -->
  <table width="100%" cellpadding="0" cellspacing="0">
  <tr>
  <td align="center" style="font-size:7.5px; line-height:1.5; padding:4pt 0 6pt 0;">
    ~~ Terima kasih ~~<br>
    Sayur Segar, Pilihan Juara!
  </td>
  </tr>
  </table>

</td>
</tr>
</table>

</body>
</html>
