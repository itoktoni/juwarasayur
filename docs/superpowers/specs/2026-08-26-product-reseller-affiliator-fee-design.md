# Design Spec — Product Fee Reseller vs Affiliator (Opsi A)

**Date:** 2026-08-26
**Project:** itoktoni/starterkit — Laravel 13 Livewire CMS + WMS
**Feature:** Tambah fee persentase per-product untuk reseller (diskon harga) & affiliator (komisi withdraw), order berbeda berdasarkan `auth()->user()->type`.
**Decision:** Opsi A Dual-kolom minimal (approve 2026-08-26).

## 1. Context & Goals
- Existing: `catalog_products` tanpa fee per-role; `users` punya `fee decimal(5,2) nullable` + `UserTypeEnum {user,reseller,customer}` + `config('commission.rate')` global 2%; `Withdrawal::earned()` = `sum(so_grand_total)*effectiveFee/100`; `So/SoDetail` menyimpan order.
- Goal: setiap product punya `reseller_fee_percent` & `affiliator_fee_percent` (0-100, nullable). Jika login **affiliator** → dapat komisi withdraw seperti reseller sekarang. Jika login **reseller** → dapat harga diskon `harga_bayar = harga - harga*fee%/100`, **tidak** dapat dashboard fee/withdraw. Format harga Rupiah Indonesia.
- Non-goals: ledger terpisah, attribution cookie, auto-mutasi fee (ditunda).

## 2. Architecture Overview
```
User (type=reseller|affiliator|...) --fee--> FeeResolver --product fee--> Price/Commission
Product (catalog_products) --reseller_fee%, affiliator_fee%--> Resolver
SoDetail (so_order_details) --snapshot fee_percent/amount/source/role--> Withdrawal (hanya affiliator)
So (so_orders) --grand_total dari hargaEfektif--> Invoice
```

## 3. Data Model Changes

### 3.1 Enum & User
- File: `app/Enums/UserTypeEnum.php` — tambah `const AFFILIATOR='affiliator'`, `getDescription()` => 'Affiliator'.
- File: `app/Models/User.php` — tambah `isAffiliator(): bool` (`type === AFFILIATOR`), keep `isReseller()`. `rules()` auto `in:getValues()`. `effectiveFee()` tetap untuk affiliator fallback.
- No change ke `users` table column `fee` (dipakai sebagai L1 untuk affiliator).

### 3.2 Product
- Migration: `2026_08_26_000006_add_reseller_affiliator_fee_to_catalog_products.php`
  - `reseller_fee_percent decimal(5,2) nullable after product_harga_grosir` — diskon reseller, null=0.
  - `affiliator_fee_percent decimal(5,2) nullable after reseller_fee_percent` — komisi affiliator, null fallback.
- Model: `Modules/Catalog/Models/Product.php`
  - `#[Fillable]` tambah 2 field, `casts()` decimal:2, `rules()` `nullable|numeric|between:0,100`.
  - `field_name()` tetap `product_nama`.

### 3.3 Order Detail Snapshot
- Migration: `2026_08_26_000007_add_fee_snapshot_to_so_order_details.php`
  - `fee_percent decimal(5,2) nullable`, `fee_amount decimal(14,2) default 0`, `fee_source string(20) nullable` (product|user|config), `applied_role string(20) nullable` (reseller|affiliator), all after `so_detail_harga`.
  - Snapshot **hanya diisi jika affiliator**; reseller `fee_amount=0`, `fee_source=null`, harga diskon disimpan di `so_detail_harga`.
- Model: `Modules/So/Models/SoDetail.php` — tambah fillable 4 kolom, casts decimal:2, rules nullable antara 0-100 untuk fee_percent.

## 4. Business Logic — FeeResolver

### 4.1 Service: `app/Services/Commission/FeeResolver.php`
Pure service, no Auth facade, injectable.
```php
final class FeeResolver {
  public function resolve(Product $product, ?User $user): array {
    // returns ['percent'=>float,'amount'=>float,'source'=>?string,'role'=>?string,'hargaEfektif'=>float]
  }
}
```
- Jika `user->isAffiliator()`: `percent = product.affiliator_fee_percent ?? user.effectiveFee()` (L2→L1→L3), `amount = harga * qty * percent/100`, `hargaEfektif = harga`, `source = product|user|config`.
- Jika `user->isReseller()`: `percent = product.reseller_fee_percent ?? 0`, `hargaEfektif = harga * (1 - percent/100)`, `amount = 0`, `source = product|none`, `role = reseller`.
- Jika guest / user biasa: `percent=0`, `hargaEfektif=harga`, `amount=0`.
- Clamp percent 0-100, throw `InvalidArgumentException` jika invalid.

### 4.2 Integration Points
- `Modules/So/Http/Controllers/SoController::postCreate/postUpdate` dan `Modules/Ecommerce/Http/Controllers/CartController@checkout` — sebelum `CreateAction::run()`, loop `details[]`, panggil resolver, overwrite `so_detail_harga = hargaEfektif` untuk reseller, dan merge `fee_*` untuk affiliator. Client payload `fee_*` di-ignore (server-side only).
- `So::recalculateTotals()` tetap sum `qty * so_detail_harga` (sudah diskon untuk reseller).
- `App\Models\Withdrawal` — update `earned(User $affiliator)`: jika `isAffiliator()`, `sum(fee_amount)` dari `so_order_details` join `so_orders` where `so_orders.so_id_reseller` atau `so_id_affiliator` (akan tambah kolom `so_id_affiliator` nullable FK users.id) dan `so_status != cancelled`. Fallback ke rumus lama jika `fee_amount` null untuk data lama. `withdrawn()` & `balance()` unchanged. `isReseller()` => `earned=0`, Policy block `create`.
  - *Alternatif sementara tanpa kolom so_id_affiliator*: pakai `so_id_reseller` yang diisi affiliator id saat login affiliator (reuse existing FK). Spec ini tambah `so_id_affiliator` nullable untuk kejelasan, dual FK.
- Migration optional: `so_orders.so_id_affiliator nullable FK users` (jika disetujui, else reuse `so_id_reseller`).

### 4.3 Immutability & Audit
- Fee snapshot immutable: `SoDetail::booted()` guard `updating` throw jika `fee_percent/amount` diubah setelah `created`. Historical order tidak berubah jika product fee diubah.

## 5. Authorization & Policies
- `app/Policies/ProductPolicy.php extends BasePolicy` — action `updateFee` hanya `admin,developer` (via `config/permision.php` tambah `'catalog.product' => ['table','create','update','delete','updateFee']`).
- `app/Policies/WithdrawalPolicy.php` (baru atau BasePolicy) — `create` hanya `isAffiliator()` atau `isAdmin()` manage; `isReseller()` 403.
- `GeneralRequest::authorize()` otomatis cek `can(updateFee, Product)` untuk field fee.

## 6. UI Changes
- Product form: `resources/views/pages/catalog/product/form.blade.php`
  - Dua `<x-input type="number" step="0.01" min="0" max="100">` label Diskon Reseller (%) & Komisi Affiliator (%), helper Rupiah, col 6+6, `@can('updateFee')` else readonly.
  - `Controller::share()` tidak perlu opsi, validation dari `Product::rules()`.
- Order table/invoice:
  - Reseller view: harga coret `formatAngka()` + harga bayar + badge diskon X%.
  - Affiliator view: kolom Komisi per baris `fee_amount` + footer total komisi (Rp).
  - Menu: hide `Withdraw` untuk reseller (blade `@if(auth()->user()->isAffiliator())`).
- File upload pattern unchanged.

## 7. Error Handling & Validation
- Model rules `between:0,100` + DB decimal(5,2) + Resolver clamp. Invalid -> `ValidationException`.
- Client tampering: resolver ignore `request->input('fee_*')`.
- Null vs 0: `null` = fallback, `0` = explicit 0% (tidak fallback). Resolver bedakan.
- Migration nullable => existing products safe.

## 8. Testing Plan
- Pest unit: `FeeResolverTest` — matrix 8 cases: reseller 0/10/100%, affiliator L2 hit, L1 fallback, L3 fallback, guest, qty>1, clamp >100.
- Pest feature: `ProductFeeValidationTest` — cannot set 101%, nullable ok.
- Pest model: `SoDetailSnapshotTest` — affiliator snapshot persisted, reseller harga diskon 90k dari 100k.
- Pest policy: `WithdrawalTest` — affiliator can withdraw, reseller 403, admin can manage.
- Dusk: product form inputs visible readonly untuk reseller/affiliator, editable untuk admin.

## 9. Rollout & Migration
1. Add enum + migrations, run `php artisan migrate`.
2. Deploy FeeResolver + Controller integration behind feature flag `if (auth()->user())`.
3. Backfill: no backfill needed (nullable). Existing orders `fee_amount` null fallback ke old formula for affiliator (temporarily).
4. No breaking change to `catalog_products` API; new fields optional.

## 10. Open Decisions Locked
- Opsi A chosen, B/C rejected. Hierarchical L1 users.fee → L2 product → L3 config for affiliator only.
- Reseller = discount price, not commission. Rupiah formatting via `formatAngka()` & `formatQty()`.
- Snapshot per SoDetail, not per So header.

## 11. Future Unlocks (out-of-scope)
- `commission_ledger` materialized view, chain-of-responsibility rule pipeline, approval workflow for fee edits, analytics daily stats.
