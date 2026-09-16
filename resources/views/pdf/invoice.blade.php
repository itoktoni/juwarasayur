<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Invoice {{ $so->so_code }}</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  @page { margin:16px 14px; }
  body { font-family:'Helvetica','Arial',sans-serif; font-size:11px; color:#1a1a1a; padding:8px 32px; }
  .header { margin-bottom:10px; border-bottom:3px solid #1a1a1a; padding-bottom:8px; }
  .brand { font-size:15px; font-weight:bold; text-transform:uppercase; letter-spacing:1px; }
  .brand-sub { font-size:11px; margin-top:2px; }
  .meta { font-size:10px; color:#333; margin-top:2px; }
  .info-label { font-size:10px; color:#333; }
  .info-value { font-size:11px; font-weight:bold; margin-top:1px; }
  .cust-label { font-size:11px; width:130px; }
  .cust-sep { width:12px; font-size:11px; }
  .section-title { font-size:13px; font-weight:bold; margin:12px 0 4px 0; }
  table.items { width:100%; border-collapse:collapse; }
  table.items th { background:#1a1a1a; color:#fff; font-weight:bold; text-align:left; padding:5px 8px; font-size:10.5px; }
  table.items td { padding:4px 8px; border:1px solid #1a1a1a; font-size:10.5px; }
  table.items tr.total td { font-weight:bold; }
  .num { text-align:right; white-space:nowrap; }
  .center { text-align:center; }
  .pay-title { font-size:11px; margin-top:14px; }
  .pay-line { font-size:11px; margin-top:2px; }
  .pay-note { font-size:11px; margin-top:10px; }
  .qr-label { font-size:11px; font-weight:bold; }
</style>
</head>
<body>
@php
  $bulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
  $tglId = function ($d) use ($bulan) {
      if (empty($d)) return '-';
      $c = $d instanceof \Carbon\Carbon ? $d : \Carbon\Carbon::parse($d);
      return $c->format('j').' '.$bulan[(int) $c->format('n')].' '.$c->format('Y');
  };
  $tglInvoice = $tglId($so->so_tanggal ?? $so->created_at);
  $tglKirim = $tglId(($so->so_tanggal ?? $so->created_at) ? \Carbon\Carbon::parse($so->so_tanggal ?? $so->created_at)->addDay() : null);
  $namaCust = $so->so_customer_name ?: ($so->has_customer?->name ?? '-');
  $telpCust = $so->so_customer_phone ?: ($so->has_customer?->phone ?? '-');
  $alamatCust = $so->so_address ?: ($so->has_customer?->address ?? '-');
  $fmt = fn ($v) => number_format((float) $v, 0, '.', ',');
  // Customer reseller (grosir) → nama produk versi grosir
  $isGrosirOrder = ($so->has_customer?->type === \App\Enums\UserTypeEnum::RESELLER);
  $namaProduk = fn ($d) => $isGrosirOrder ? ($d->has_product?->nama_grosir ?? $d->has_product?->product_nama ?? '-') : ($d->has_product?->product_nama ?? '-');
  $logoFile = null;
  if (! empty($site['logo'])) {
      $candidate = public_path(ltrim(\App\Models\WebsiteSetting::fileUrl($site['logo']), '/'));
      $candidate = str_replace('\\', '/', $candidate);
      if (is_file($candidate)) $logoFile = $candidate;
  }
@endphp

<!-- ========== HEADER ========== -->
<table class="header" width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td width="130" valign="middle">
      @if($logoFile)
        <img src="{{ $logoFile }}" style="width:120px;">
      @else
        <div class="brand" style="color:#2e7d32;">{{ strtoupper($site['name'] ?? 'JUWARA SAYUR') }}</div>
      @endif
    </td>
    <td valign="middle" style="padding-left:10px;">
      <div class="brand">{{ strtoupper($site['name'] ?? 'JUWARASAYUR.ID') }}</div>
      <div class="meta">Kualitas Juwara, Segar Setiap Hari</div>
      <div class="meta">{{ $site['alamat'] ?: 'Jl. Masjid Al-Huda no.93' }} &nbsp;||&nbsp; Call us : {{ $site['telepon'] ?: '0851 1103 7383' }}</div>
    </td>
  </tr>
</table>

<!-- ========== INFO INVOICE ========== -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-top:8px;">
  <tr>
    <td width="33%">
      <div class="info-label">Nomor Invoice</div>
      <div class="info-value">{{ $so->so_code }}</div>
    </td>
    <td width="33%">
      <div class="info-label">Tanggal Invoice</div>
      <div class="info-value">{{ $tglInvoice }}</div>
    </td>
    <td width="34%">
      <div class="info-label">Tanggal Pengiriman</div>
      <div class="info-value">{{ $tglKirim }}</div>
    </td>
  </tr>
</table>

<!-- ========== CUSTOMER ========== -->
<table cellpadding="0" cellspacing="0" style="margin-top:10px;">
  <tr>
    <td class="cust-label">Nama Pelanggan</td>
    <td class="cust-sep">:</td>
    <td>{{ $namaCust }}</td>
  </tr>
  <tr>
    <td class="cust-label">No Telepon</td>
    <td class="cust-sep">:</td>
    <td>{{ $telpCust }}</td>
  </tr>
  <tr>
    <td class="cust-label" valign="top">Alamat Pengiriman</td>
    <td class="cust-sep" valign="top">:</td>
    <td>{{ $alamatCust }}</td>
  </tr>
</table>

<!-- ========== DETAIL PESANAN ========== -->
<div class="section-title">Detail Pesanan</div>
<table class="items" cellpadding="0" cellspacing="0">
  <thead>
    <tr>
      <th width="30" class="center">No</th>
      <th>Product</th>
      <th width="45" class="center">Qty</th>
      <th width="90" class="num">Harga</th>
      <th width="100" class="num">Total</th>
    </tr>
  </thead>
  <tbody>
    @foreach($so->has_details as $d)
    <tr>
      <td class="center">{{ $loop->iteration }}</td>
      <td>{{ strtoupper($namaProduk($d)) }}@if($d->so_detail_keterangan) <span style="font-size:9px;">({{ $d->so_detail_keterangan }})</span>@endif</td>
      <td class="center">{{ $d->so_detail_qty }}</td>
      <td class="num">{{ $fmt($d->so_detail_harga) }}</td>
      <td class="num">{{ $fmt((int) $d->so_detail_qty * (float) $d->so_detail_harga) }}</td>
    </tr>
    @endforeach
    @if((float) $so->so_discount > 0)
      @php $diskonAmount = max(0, (float) $so->so_subtotal - (float) $so->so_dpp); @endphp
    <tr>
      <td colspan="4" class="center">Diskon{{ $so->so_discount_type === 'percent' ? ' '.$so->so_discount.'%' : '' }}</td>
      <td class="num">-{{ $fmt($diskonAmount) }}</td>
    </tr>
    @endif
    @if((float) $so->so_ppn > 0)
    <tr>
      <td colspan="4" class="center">PPN {{ rtrim(rtrim((string) $so->so_ppn_rate, '0'), '.') }}%</td>
      <td class="num">{{ $fmt($so->so_ppn) }}</td>
    </tr>
    @endif
    @if((float) $so->so_pph > 0)
    <tr>
      <td colspan="4" class="center">PPh {{ rtrim(rtrim((string) $so->so_pph_rate, '0'), '.') }}%</td>
      <td class="num">{{ $fmt($so->so_pph) }}</td>
    </tr>
    @endif
    @if((float) $so->so_shipping_fee > 0)
    <tr>
      <td colspan="4" class="center">Ongkir</td>
      <td class="num">{{ $fmt($so->so_shipping_fee) }}</td>
    </tr>
    @endif
    <tr class="total">
      <td colspan="4" class="center" style="font-size:12px;">Total Harga</td>
      <td class="num" style="font-size:12px;">{{ $fmt($so->so_grand_total) }}</td>
    </tr>
  </tbody>
</table>

<!-- ========== PEMBAYARAN ========== -->
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top" width="60%">
      <div class="pay-title">Pembayaran</div>
      <div class="pay-line">Silakan melakukan pembayaran melalui rekening atau QRIS berikut:</div>
      <table cellpadding="0" cellspacing="0" style="margin-top:4px;">
        <tr>
          <td class="cust-label">Bank</td>
          <td class="cust-sep">:</td>
          <td>{{ $site['bank_name'] ?? 'BCA' }}</td>
        </tr>
        <tr>
          <td class="cust-label">No. Rekening</td>
          <td class="cust-sep">:</td>
          <td>{{ $site['bank_account_no'] ?? '3452301226' }}</td>
        </tr>
        <tr>
          <td class="cust-label">Atas Nama</td>
          <td class="cust-sep">:</td>
          <td>{{ $site['bank_account_name'] ?? 'Deny Irawan' }}</td>
        </tr>
      </table>
      <div class="pay-note">Mohon konfirmasi setelah melakukan pembayaran<br>ke nomor admin {{ strtolower($site['name'] ?? 'juwarasayur.id') }}</div>
    </td>
    <td valign="top" width="40%" align="center">
      @if(! empty($qrDataUri))
        <div class="qr-label" style="margin-top:14px;">{{ strtoupper($site['name'] ?? 'JUWARASAYUR.ID') }}</div>
        <img src="{{ $qrDataUri }}" style="width:150px; height:150px; margin-top:4px;">
        <div class="qr-label" style="margin-top:4px;">QRIS</div>
      @endif
    </td>
  </tr>
</table>

</body>
</html>
