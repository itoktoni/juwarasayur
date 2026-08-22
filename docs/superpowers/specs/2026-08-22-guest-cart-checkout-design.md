# Design: Guest Session Cart + Checkout Pickup/COD + QRIS Mockup

**Tanggal:** 2026-08-22 (revisi final)
**Modul:** `Modules/Ecommerce` (+ kecil di `Modules/So`)
**Status:** Disetujui user

## Masalah

Flow ecommerce saat ini salah: cart dan checkout wajib login, cart tersimpan di DB per
`user_id`, checkout hanya pickup + nama/HP, tidak ada data pengiriman & pembayaran.

Flow yang benar: customer **tanpa login** add-to-cart (session browser) → di ujung
checkout isi nama + HP → pilih pengiriman (pickup / COD dengan lokasi GPS) → bayar
via halaman QRIS mockup.

## Keputusan (disetujui user)

| Topik | Keputusan |
|---|---|
| Cart guest | Session array Laravel (`session('cart')` = `[product_id => qty]`) |
| Login | Hybrid — guest bebas; saat login, session cart di-merge ke DB cart user |
| Pengiriman | **Pickup + COD saja** (tanpa metode "dikirim" terpisah) |
| Ongkir | Dihitung dari **jarak ke titik COD terdekat** — tanpa RajaOngkir |
| Titik COD | Banyak titik, disimpan di **tabel DB** `so_cod_locations` (nama, alamat, lat, lng) |
| Tarif | Fee flat per titik (`so_cod_locations.fee`); jika null → dihitung dari jarak memakai `price_per_km`/`min_fee` settings yang sudah ada |
| Pembayaran | QRIS mockup, timer **5 menit**, tombol simulasi bayar |

## Arsitektur

### 1. CartService (`Modules/Ecommerce/app/Services/CartService.php`)

Satu pintu operasi cart; controller tidak tahu bedanya guest/login.

```php
items(): Collection        // gabung sumber sesuai state auth
add(Product $p, int $qty): void
updateQty(array $qtyMap): void
remove(int $productId): void
count(): int
subtotal(): float
mergeSessionToDb(): void   // dipanggil event Login
```

### 2. Routing

Public (tanpa auth): `shop.*`, `cart.*`, `/checkout`, `/payment/*`.
Tetap auth: `ecommerce.orders.*`.

Route baru:
- `POST /checkout/cod-quote` — body `{lat, lng}` → titik COD terdekat + jarak + ongkir
- `GET /payment/{id}` — halaman QRIS mockup
- `POST /payment/{id}/simulate` — tandai lunas

### 3. Tabel DB baru (migrasi modul Ecommerce)

```php
// so_shipping_rates — tarif pengiriman
Schema::create('so_shipping_rates', ...);
    $table->string('rate_method', 20);      // pickup | cod
    $table->decimal('rate_base_fee', 12, 2)->default(0);
    $table->decimal('rate_per_km', 12, 2)->default(0);
    $table->boolean('is_active')->default(true);

// so_cod_locations — banyak titik COD
Schema::create('so_cod_locations', ...);
    $table->string('location_name', 100);
    $table->text('address')->nullable();
    $table->decimal('lat', 10, 7);
    $table->decimal('lng', 10, 7);
    $table->boolean('is_active')->default(true);
```

Seeder: `pickup` (0/0), `cod` (base 5.000 / km 2.000), 2–3 titik COD contoh.

### 4. Perhitungan ongkir COD (ShippingService)

1. Customer klik "Gunakan Lokasi Saya" → Geolocation API → lat/lng.
2. `POST /checkout/cod-quote` → server cari titik aktif **terdekat**
   (haversine, dihitung di PHP — tabel kecil).
3. Response: `{name, address, distance_km, fee}`; fee = `base_fee + ceil(km) * per_km`.
4. Submit: server **menghitung ulang** dari lat/lng (fee dari klien tidak dipercaya),
   simpan `so_cod_location` (nama titik), `so_distance_km`, `so_shipping_fee`,
   `so_lat`, `so_lng`, alamat teks opsional.

### 5. Checkout

Form: Nama*, HP*, radio Pickup/COD; jika COD → tombol ambil lokasi GPS +
hasil (titik terdekat, jarak, ongkir) + alamat detail opsional.
Validasi: `required_if` lat/lng untuk COD. Order dibuat `so_status=PENDING`;
guest → `so_id_customer=null`. Id SO disimpan ke `session('guest_orders')`
untuk akses halaman pembayaran/sukses oleh guest. Cart dikosongkan setelah order.

### 6. Migrasi modul So

- `so_id_reseller` → nullable (order guest).
- `SoStatusEnum` tambah `PAID = 'paid'` ("Dibayar").

### 7. Halaman pembayaran QRIS mockup

QRIS dummy + countdown **5 menit** (mm:ss); habis → tombol disabled,
pesanan tetap PENDING. Tombol "Simulasi Bayar" (hanya saat PENDING) → status
`PAID` → halaman sukses (kode SO, total, metode kirim, item).

## Error handling

- Produk nonaktif/stok kurang saat submit → `withErrors(['cart'])`.
- Guest akses payment SO milik orang lain / sudah PAID → 403/422.
- GPS ditolak/di luar radius → tampilkan pesan; COD tidak bisa dipilih.
- product_id basi di session → difilter di `items()`.

## Testing

1. Guest add-to-cart → session terisi; count benar.
2. Login dengan session cart → merge ke DB.
3. Quote COD: titik terdekat & fee sesuai tabel; submit menghitung ulang server-side.
4. Validasi wajib GPS utk COD; pickup tidak butuh.
5. Simulate bayar → PAID; simulate kedua kali → gagal.

## Out of scope

- Payment gateway nyata, RajaOngkir, admin UI kelola titik COD/tarif (via seeder/DB dulu).
