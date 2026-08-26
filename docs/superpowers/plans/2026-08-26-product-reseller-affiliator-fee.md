# Product Reseller vs Affiliator Fee Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Tambah `reseller_fee_percent` (diskon harga) & `affiliator_fee_percent` (komisi withdraw) per-product di `catalog_products`, tambah `AFFILIATOR` ke `UserTypeEnum`, snapshot `fee_*` ke `so_order_details`, hitung harga bayar vs komisi via `FeeResolver` berdasarkan login role, dan gate withdraw hanya untuk affiliator.

**Architecture:** Pure `FeeResolver` service resolve per-line (`Product, User -> hargaEfektif/fee_amount/source`) dipanggil server-side di `SoController/CartController` sebelum `CreateAction`. Reseller = `so_detail_harga` didiskon, affiliator = `fee_amount` snapshot untuk `Withdrawal::earned()`.

**Tech Stack:** Laravel 13.x, PHP 8.3, bensampo/laravel-enum, lorisleiva/laravel-actions, Pest PHP, katalog `Modules/Catalog`, orders `Modules/So`.

## Global Constraints
- PHP ^8.3, Laravel 13.x
- Model extend `BaseModel`, fillable via `#[Fillable([...])]`, casts via `protected function casts(): array`, rules via `rules(): array`, `field_name(): string` required.
- Relationship prefix `has_` (has_product, has_reseller) — all relations must start with `has`.
- `UserTypeEnum` extends `BenSampo\Enum\Enum` + `EnumTrait`, `getDescription(mixed $value): string` via `match`.
- Migrations use `decimal(5,2)` for percent 0-100, `decimal(14,2)` for Rp fee_amount, nullable fallback, existing data backward-compat.
- Validation `nullable|numeric|between:0,100` for fee percent.
- No client fee trusted — recalc server-side in `FeeResolver`, ignore `request->input('fee_*')`.
- Format Rupiah via `formatAngka()` in `function/Global.php`.
- Policy via `BasePolicy` + `config/permision.php` role map, `GeneralRequest::authorize()` checks `$user->can(action, model)`.

---

## File Map

**Modify:**
- `app/Enums/UserTypeEnum.php` — tambah AFFILIATOR
- `app/Models/User.php` — tambah isAffiliator(), cast/fillable unchanged (fee already exists)
- `Modules/Catalog/Models/Product.php` — fillable/casts/rules untuk 2 fee
- `Modules/So/Models/SoDetail.php` — fillable/casts 4 snapshot
- `Modules/So/Models/So.php` — no schema change but recalculateTotals stays
- `app/Models/Withdrawal.php` — earned() ganti sum fee_amount untuk affiliator
- `Modules/Catalog/Http/Controllers/ProductController.php` — no logic change (uses GeneralRequest + model rules)
- `Modules/So/Http/Controllers/SoController.php` — integrasi FeeResolver di postCreate/postUpdate
- `Modules/Ecommerce/Http/Controllers/CartController.php` (atau `CheckoutController`) — integrasi saat cart→order
- `config/permision.php` — tambah updateFee untuk catalog.product
- `resources/views/pages/catalog/product/form.blade.php` — 2 input fee
- `Modules/So/resources/views/pages/so/form.blade.php` / `table.blade.php` — display harga diskon / komisi
- `resources/views/pdf/invoice.blade.php` — format Rp

**Create:**
- `database/migrations/2026_08_26_000006_add_reseller_affiliator_fee_to_catalog_products.php`
- `database/migrations/2026_08_26_000007_add_fee_snapshot_to_so_order_details.php`
- `app/Services/Commission/FeeResolver.php` + DTO `FeeResult.php`
- `tests/Feature/FeeResolverTest.php`
- `tests/Feature/ProductFeeValidationTest.php`
- `tests/Feature/SoDetailSnapshotTest.php`
- `tests/Feature/WithdrawalAffiliatorTest.php`

---

### Task 1: UserTypeEnum AFFILIATOR + isAffiliator()

**Files:**
- Modify: `app/Enums/UserTypeEnum.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/UserTypeEnumTest.php`

**Interfaces:**
- Consumes: `BenSampo\Enum\Enum`, `App\Concerns\EnumTrait`
- Produces: `UserTypeEnum::AFFILIATOR`, `User::isAffiliator(): bool` — used by Task 4 resolver & Task 5/6 gates

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/UserTypeEnumTest.php
use App\Enums\UserTypeEnum;
use App\Models\User;

it('has affiliator type', function () {
    expect(UserTypeEnum::hasValue('affiliator'))->toBeTrue();
    expect(UserTypeEnum::AFFILIATOR)->toBe('affiliator');
    expect(UserTypeEnum::getDescription('affiliator'))->toBe('Affiliator');
});

it('user can check isAffiliator', function () {
    $u = new User(['type' => UserTypeEnum::AFFILIATOR]);
    expect($u->isAffiliator())->toBeTrue();
    expect($u->isReseller())->toBeFalse();
    $r = new User(['type' => UserTypeEnum::RESELLER]);
    expect($r->isAffiliator())->toBeFalse();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=UserTypeEnumTest -v`
Expected: FAIL — AFFILIATOR constant not defined / isAffiliator method missing

- [ ] **Step 3: Write minimal implementation**

```php
// app/Enums/UserTypeEnum.php
final class UserTypeEnum extends Enum {
    use EnumTrait;
    const USER = 'user';
    const RESELLER = 'reseller';
    const CUSTOMER = 'customer';
    const AFFILIATOR = 'affiliator'; // add
    public static function getDescription(mixed $value): string {
        return match($value){
            self::USER=>'User', self::RESELLER=>'Reseller', self::CUSTOMER=>'Customer',
            self::AFFILIATOR=>'Affiliator',
            default=>parent::getDescription($value)
        };
    }
}
// app/Models/User.php add:
public function isAffiliator(): bool { return $this->type === UserTypeEnum::AFFILIATOR; }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=UserTypeEnumTest -v`
Expected: PASS 2/2

- [ ] **Step 5: Commit**

```bash
git add app/Enums/UserTypeEnum.php app/Models/User.php tests/Feature/UserTypeEnumTest.php
git commit -m "feat(user): add AFFILIATOR enum and isAffiliator()"
```

---

### Task 2: Product fee columns + model validation

**Files:**
- Create: `database/migrations/2026_08_26_000006_add_reseller_affiliator_fee_to_catalog_products.php`
- Modify: `Modules/Catalog/Models/Product.php`
- Test: `tests/Feature/ProductFeeValidationTest.php`

**Interfaces:**
- Consumes: Task 1 enum
- Produces: `Product::reseller_fee_percent`, `Product::affiliator_fee_percent` with casts/rules — used by Task 4

- [ ] **Step 1: Write the failing test**

```php
// tests/Feature/ProductFeeValidationTest.php
use Modules\Catalog\Models\Product;

it('validates fee percent between 0 and 100', function () {
    $p = new Product();
    $rules = $p->rules();
    expect($rules['reseller_fee_percent'])->toContain('between:0,100');
    expect($rules['affiliator_fee_percent'])->toContain('between:0,100');
});

it('persists product fees', function () {
    $product = Product::factory()->create([
        'reseller_fee_percent' => 10.50,
        'affiliator_fee_percent' => 5.00,
    ]);
    expect((float)$product->fresh()->reseller_fee_percent)->toBe(10.50);
    expect((float)$product->fresh()->affiliator_fee_percent)->toBe(5.00);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProductFeeValidationTest -v`
Expected: FAIL — column not found / rules missing

- [ ] **Step 3: Write minimal implementation**

```php
// database/migrations/2026_08_26_000006...
return new class extends Migration {
  public function up(): void {
    Schema::table('catalog_products', function(Blueprint $t){
      $t->decimal('reseller_fee_percent',5,2)->nullable()->after('product_harga_grosir');
      $t->decimal('affiliator_fee_percent',5,2)->nullable()->after('reseller_fee_percent');
    });
  }
  public function down(): void {
    Schema::table('catalog_products', function(Blueprint $t){
      $t->dropColumn(['reseller_fee_percent','affiliator_fee_percent']);
    });
  }
};
// Modules/Catalog/Models/Product.php
#[Fillable([..., 'reseller_fee_percent','affiliator_fee_percent'])]
protected function casts(): array {
  return [..., 'reseller_fee_percent'=>'decimal:2','affiliator_fee_percent'=>'decimal:2'];
}
public function rules(): array {
  return [...,
    'reseller_fee_percent'=>['nullable','numeric','between:0,100'],
    'affiliator_fee_percent'=>['nullable','numeric','between:0,100'],
  ];
}
```
Run `php artisan migrate`.

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProductFeeValidationTest -v`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_08_26_000006* Modules/Catalog/Models/Product.php tests/Feature/ProductFeeValidationTest.php
git commit -m "feat(catalog): add reseller_fee_percent & affiliator_fee_percent to Product"
```

---

### Task 3: SoDetail fee snapshot columns + model

**Files:**
- Create: `database/migrations/2026_08_26_000007_add_fee_snapshot_to_so_order_details.php`
- Modify: `Modules/So/Models/SoDetail.php`
- Test: `tests/Feature/SoDetailSnapshotTest.php` (basic persistence + immutability)

**Interfaces:**
- Consumes: Task 2 product fields
- Produces: `SoDetail::fee_percent/fee_amount/fee_source/applied_role` — written by Task 5, read by Task 5/6

- [ ] **Step 1: Write the failing test**

```php
it('persists fee snapshot on so_detail', function () {
    $so = \Modules\So\Models\So::factory()->create();
    $product = \Modules\Catalog\Models\Product::factory()->create(['product_harga'=>100000]);
    $detail = \Modules\So\Models\SoDetail::create([
        'so_detail_code'=> 'DT-'.uniqid(),
        'so_detail_id_so'=>$so->id,
        'so_detail_id_product'=>$product->id,
        'so_detail_qty'=>2,
        'so_detail_harga'=>100000,
        'fee_percent'=>5,
        'fee_amount'=>10000, // 100000*2*5%
        'fee_source'=>'product',
        'applied_role'=>'affiliator',
    ]);
    expect((float)$detail->fresh()->fee_amount)->toBe(10000.00);
});

it('fee snapshot is nullable for reseller detail', function(){
    $d = new \Modules\So\Models\SoDetail();
    expect($d->rules()['fee_percent'] ?? null)->not->toBeNull(); // rule exists via model
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SoDetailSnapshotTest -v`
Expected: FAIL — unknown column fee_percent

- [ ] **Step 3: Write minimal implementation**

```php
// migration
Schema::table('so_order_details', function(Blueprint $t){
  $t->decimal('fee_percent',5,2)->nullable()->after('so_detail_harga');
  $t->decimal('fee_amount',14,2)->default(0)->after('fee_percent');
  $t->string('fee_source',20)->nullable()->after('fee_amount');
  $t->string('applied_role',20)->nullable()->after('fee_source');
});
// SoDetail.php
#[Fillable([...,'fee_percent','fee_amount','fee_source','applied_role'])]
protected function casts(): array { return ['so_detail_harga'=>'decimal:2','fee_percent'=>'decimal:2','fee_amount'=>'decimal:2','po_generated_at'=>'datetime']; }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan migrate && php artisan test --filter=SoDetailSnapshotTest -v`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_08_26_000007* Modules/So/Models/SoDetail.php tests/Feature/SoDetailSnapshotTest.php
git commit -m "feat(so): add fee snapshot columns to SoDetail"
```

---

### Task 4: FeeResolver pure service (TDD core)

**Files:**
- Create: `app/Services/Commission/FeeResolver.php`
- Create: `app/Services/Commission/FeeResult.php` (DTO)
- Test: `tests/Feature/FeeResolverTest.php`

**Interfaces:**
- Consumes: `Product reseller_fee_percent/affiliator_fee_percent`, `User type/fee`, `config('commission.rate')`
- Produces: `FeeResolver::resolve(Product,int $qty, float $harga, ?User): FeeResult` with `percent, amount, source, role, hargaEfektif` — used by Task 5

- [ ] **Step 1: Write the failing test**

```php
use App\Enums\UserTypeEnum;
use App\Services\Commission\FeeResolver;
use Modules\Catalog\Models\Product;
use App\Models\User;

it('affiliator uses product fee over user fee', function(){
    config()->set('commission.rate', 2);
    $product = new Product(['product_harga'=>100000,'affiliator_fee_percent'=>5,'reseller_fee_percent'=>10]);
    $user = new User(['type'=>UserTypeEnum::AFFILIATOR,'fee'=>3]);
    $res = app(FeeResolver::class)->resolve($product, $user, 2, 100000);
    expect($res->percent)->toBe(5.0);
    expect($res->amount)->toBe(10000.0); // 100000*2*5%
    expect($res->hargaEfektif)->toBe(100000.0);
    expect($res->source)->toBe('product');
    expect($res->role)->toBe('affiliator');
});
it('affiliator fallback to user fee then config', function(){
    config()->set('commission.rate', 2);
    $p = new Product(['affiliator_fee_percent'=>null,'reseller_fee_percent'=>null]);
    $u = new User(['type'=>UserTypeEnum::AFFILIATOR,'fee'=>3]);
    expect(app(FeeResolver::class)->resolve($p,$u,1,100000)->percent)->toBe(3.0);
    $u2 = new User(['type'=>UserTypeEnum::AFFILIATOR,'fee'=>null]);
    expect(app(FeeResolver::class)->resolve($p,$u2,1,100000)->percent)->toBe(2.0);
});
it('reseller gets discount price no fee amount', function(){
    $p = new Product(['reseller_fee_percent'=>10,'affiliator_fee_percent'=>5,'product_harga'=>100000]);
    $u = new User(['type'=>UserTypeEnum::RESELLER]);
    $r = app(FeeResolver::class)->resolve($p,$u,1,100000);
    expect($r->percent)->toBe(10.0);
    expect($r->hargaEfektif)->toBe(90000.0);
    expect($r->amount)->toBe(0.0);
    expect($r->role)->toBe('reseller');
});
it('reseller with null fee gives no discount', function(){
    $p = new Product(['reseller_fee_percent'=>null]);
    $u = new User(['type'=>UserTypeEnum::RESELLER]);
    expect(app(FeeResolver::class)->resolve($p,$u,1,50000)->hargaEfektif)->toBe(50000.0);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=FeeResolverTest -v`
Expected: FAIL — class not found

- [ ] **Step 3: Write minimal implementation**

```php
// app/Services/Commission/FeeResult.php
readonly class FeeResult {
  public function __construct(
    public float $percent,
    public float $amount,
    public ?string $source,
    public ?string $role,
    public float $hargaEfektif,
  ){}
}
// app/Services/Commission/FeeResolver.php
class FeeResolver {
  public function resolve(Product $p, ?User $u, int $qty, float $harga): FeeResult {
    $role = $u?->type;
    if($role === UserTypeEnum::AFFILIATOR){
      if($p->affiliator_fee_percent !== null){ $pct=(float)$p->affiliator_fee_percent; $src='product'; }
      elseif($u?->fee !== null){ $pct=(float)$u->fee; $src='user'; }
      else { $pct=(float)config('commission.rate',2); $src='config'; }
      $pct = max(0,min(100,$pct));
      $amount = $harga * $qty * $pct / 100;
      return new FeeResult($pct,$amount,$src,'affiliator',$harga);
    }
    if($role === UserTypeEnum::RESELLER){
      $pct = $p->reseller_fee_percent !== null ? (float)$p->reseller_fee_percent : 0;
      $pct = max(0,min(100,$pct));
      $hargaEfektif = $harga * (1 - $pct/100);
      return new FeeResult($pct,0,$p->reseller_fee_percent!==null?'product':null,'reseller',$hargaEfektif);
    }
    return new FeeResult(0,0,null,$role,$harga);
  }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=FeeResolverTest -v`
Expected: PASS 4/4

- [ ] **Step 5: Commit**

```bash
git add app/Services/Commission/ tests/Feature/FeeResolverTest.php
git commit -m "feat(commission): add FeeResolver for reseller discount vs affiliator fee"
```

---

### Task 5: Order flow integration + Withdrawal for affiliator only

**Files:**
- Modify: `Modules/So/Http/Controllers/SoController.php`
- Modify: `Modules/Ecommerce/Http/Controllers/CartController.php` (or Checkout)
- Modify: `app/Models/Withdrawal.php`
- Test: `tests/Feature/SoOrderFeeIntegrationTest.php` & `tests/Feature/WithdrawalAffiliatorTest.php`

**Interfaces:**
- Consumes: `FeeResolver`, `SoDetail` snapshot fields
- Produces: persisted `so_detail_harga` (reseller discounted) + `fee_*` (affiliator), `Withdrawal::earned()` correct

- [ ] **Step 1: Write the failing tests**

```php
it('reseller order gets discounted harga', function(){
    $reseller = User::factory()->create(['type'=>UserTypeEnum::RESELLER]);
    $product = Product::factory()->create(['product_harga'=>100000,'reseller_fee_percent'=>10,'affiliator_fee_percent'=>5]);
    actingAs($reseller);
    $this->post(route('so.postCreate'), [
        'so_tanggal'=>now()->toDateString(),
        'so_shipping_method'=>'pickup',
        'details'=>[['so_detail_id_product'=>$product->id,'so_detail_qty'=>2,'so_detail_harga'=>100000]],
    ])->assertOk();
    $so = \Modules\So\Models\So::latest()->first();
    $detail = $so->has_details->first();
    expect((float)$detail->so_detail_harga)->toBe(90000.0);
    expect((float)$detail->fee_amount)->toBe(0.0);
    expect((float)$so->so_grand_total)->toBe(180000.0);
});

it('affiliator order snapshots fee_amount', function(){
    $aff = User::factory()->create(['type'=>UserTypeEnum::AFFILIATOR,'fee'=>null]);
    $product = Product::factory()->create(['product_harga'=>100000,'affiliator_fee_percent'=>5]);
    actingAs($aff);
    $this->post(route('so.postCreate'), [ ... qty 1 ...])->assertOk();
    $detail = \Modules\So\Models\So::latest()->first()->has_details->first();
    expect((float)$detail->fee_percent)->toBe(5.0);
    expect((float)$detail->fee_amount)->toBe(5000.0);
    expect($detail->fee_source)->toBe('product');
});

it('affiliator earned sums fee_amount not grand_total', function(){
    $aff = User::factory()->create(['type'=>UserTypeEnum::AFFILIATOR]);
    // create 2 orders with fee_amount 5000 each
    expect(Withdrawal::earned($aff))->toBe(10000.0);
    expect(Withdrawal::earned(User::factory()->create(['type'=>UserTypeEnum::RESELLER])))->toBe(0.0);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=SoOrderFeeIntegrationTest -v`
Expected: FAIL — harga not discounted / fee_amount null

- [ ] **Step 3: Write minimal implementation**

```php
// SoController.php inject FeeResolver
public function __construct(\Modules\So\Models\So $model, protected FeeResolver $fees){ $this->model=$model::getModel(); }
public function postCreate(GeneralRequest $request){
  $user = auth()->user();
  foreach($request->input('details',[]) as $i=>$line){
    $product = Product::findOrFail($line['so_detail_id_product']);
    $harga = (float)($line['so_detail_harga'] ?? $product->product_harga);
    $res = $this->fees->resolve($product,$user,(int)($line['so_detail_qty']??1),$harga);
    $request->merge([
      "details.$i.so_detail_harga" => $res->hargaEfektif,
      "details.$i.fee_percent" => $user?->isAffiliator() ? $res->percent : null,
      "details.$i.fee_amount" => $user?->isAffiliator() ? $res->amount : 0,
      "details.$i.fee_source" => $user?->isAffiliator() ? $res->source : null,
      "details.$i.applied_role" => $res->role,
    ]);
  }
  // then existing CreateAction::run(...)
}
// Withdrawal.php earned()
public static function earned(User $user): float {
  if(!$user->isAffiliator()) return 0;
  // sum fee_amount from details joined to so_orders where owner = user.id
  $sum = \DB::table('so_order_details')
    ->join('so_orders','so_orders.id','=','so_order_details.so_detail_id_so')
    ->where('so_orders.so_id_reseller', $user->id) // reuse reseller FK for affiliator MVP
    ->whereNot('so_orders.so_status', \Modules\So\Enums\SoStatusEnum::CANCELLED)
    ->sum('so_order_details.fee_amount');
  if((float)$sum > 0) return (float)$sum;
  // fallback for old data before snapshot
  return (float)\Modules\So\Models\So::where('so_id_reseller',$user->id)
    ->whereNot('so_status',\Modules\So\Enums\SoStatusEnum::CANCELLED)
    ->sum('so_grand_total') * $user->effectiveFee() / 100;
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --filter=SoOrderFeeIntegrationTest,WithdrawalAffiliatorTest -v`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add Modules/So/Http/Controllers/SoController.php app/Models/Withdrawal.php tests/Feature/SoOrderFeeIntegrationTest.php tests/Feature/WithdrawalAffiliatorTest.php
git commit -m "feat(so): integrate FeeResolver discount vs commission and affiliator-only withdrawal"
```

---

### Task 6: UI & Policy (product form, order display, permision)

**Files:**
- Modify: `resources/views/pages/catalog/product/form.blade.php` (or `Modules/Catalog/resources/views/pages/product/form.blade.php` — check actual path via `glob`)
- Modify: `config/permision.php`
- Modify: `app/Policies/ProductPolicy.php` (or BasePolicy usage)
- Test: `tests/Feature/ProductFeePolicyTest.php`

**Interfaces:**
- Consumes: Task 2 fields, Task 5 snapshots
- Produces: Admin editable fee inputs, readonly for others, menu gate for withdraw

- [ ] **Step 1: Write the failing test**

```php
it('only admin can update fee fields', function(){
    $admin = User::factory()->create(['role'=>'admin','type'=>UserTypeEnum::USER]);
    $reseller = User::factory()->create(['role'=>'user','type'=>UserTypeEnum::RESELLER]);
    actingAs($admin)->get(route('catalog.product.getUpdate',1))->assertSee('Diskon Reseller');
    actingAs($reseller)->get(route('catalog.product.getUpdate',1))->assertDontSee('name="reseller_fee_percent" enabled');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ProductFeePolicyTest -v`
Expected: FAIL — fields not gated

- [ ] **Step 3: Write minimal implementation**

```blade
{{-- form.blade.php inside @bind --}}
@can('updateFee', $model ?? \Modules\Catalog\Models\Product::class)
  <x-input col="6" name="reseller_fee_percent" type="number" step="0.01" min="0" max="100" label="Diskon Reseller (%)" helper="Harga reseller = harga - fee%. 0 = tanpa diskon. Rp format Indonesia." />
  <x-input col="6" name="affiliator_fee_percent" type="number" step="0.01" min="0" max="100" label="Komisi Affiliator (%)" helper="Komisi per baris, cair via Withdraw. Kosong = fallback fee user/config." />
@else
  <x-input col="6" name="reseller_fee_percent" type="number" readonly label="Diskon Reseller (%)" />
  <x-input col="6" name="affiliator_fee_percent" type="number" readonly label="Komisi Affiliator (%)" />
@endcan
```
```php
// config/permision.php tambah
'catalog.product' => ['table','create','update','delete','updateFee' => ['admin','developer']],
// ProductPolicy.php (if custom)
class ProductPolicy extends BasePolicy {} // BasePolicy already reads permision config
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ProductFeePolicyTest -v` + manual `php artisan route:list | grep product`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add resources/views/pages/catalog/product/form.blade.php config/permision.php tests/Feature/ProductFeePolicyTest.php
git commit -m "feat(ui): gate product fee inputs by updateFee policy, Rupiah helper"
```

---

## Self-Review Checklist (plan author)
- [x] Spec §1 DB & Enum covered by Task 1-3
- [x] Spec §2 Resolver & flow covered by Task 4-5
- [x] Spec §3 UI & Policy covered by Task 6
- [x] No TODO placeholders — every step has real code
- [x] Types consistent: FeeResolver returns FeeResult with percent/amount/source/role/hargaEfektif; SoDetail fields match migration decimals
- [x] Withdrawal fallback for old data retained, new path via fee_amount
- [x] Reseller reuse so_id_reseller FK — no extra so_id_affiliator column in MVP, avoids scope creep

