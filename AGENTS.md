# AGENTS.md — Laravel CMS Project Guide for AI Agents

> **Project:** `itoktoni/starterkit` — Laravel 13+ Livewire CMS + WMS
> **Purpose:** Guidelines for AI agents (Zoo Code / Cline / Copilot) to create files, functions, and features consistently across this codebase.

---

## Tech Stack

| Category | Technology |
|---|---|
| **Framework** | Laravel 13.x (PHP ^8.3) |
| **Frontend** | Livewire 4.x + Flux UI 2.x + Tailwind CSS + Vite |
| **Auth** | Laravel Fortify + Sanctum (API) |
| **CMS** | Orbit (flat-file JSON, `ryangjchandler/orbit`) |
| **CRUD Actions** | `lorisleiva/laravel-actions` |
| **Auto Routes** | `izniburak/laravel-auto-routes` |
| **Real-time** | Centrifugo |
| **PDF** | `barryvdh/laravel-dompdf` |
| **Barcode** | `milon/barcode` |
| **Charts** | `arielmejiadev/larapex-charts` |
| **Testing** | Pest PHP + Laravel Dusk |
| **Enums** | `bensampo/laravel-enum` (^6.14) |
| **AI Boost** | Laravel Boost with Zoo Code agent |
| **DB** | MySQL (primary) + SQLite (Orbit flat-file) |
| **Power Joins** | `kirschbaum-development/eloquent-power-joins` |

---

## Directory Structure

```
app/
├── Actions/           # Laravel Actions (Create/Update/Delete via lorisleiva)
│   └── Fortify/       # Fortify action overrides
├── Boost/Agents/      # Laravel Boost AI agent definitions
├── Charts/            # Larapex chart classes
├── Concerns/          # Shared traits (reusable behavior)
├── Console/Commands/  # Artisan commands
├── Contracts/         # Interfaces
├── Enums/              # Enum classes (bensampo/laravel-enum)
├── Http/
│   ├── Controllers/
│   │   ├── Api/       # API controllers
│   │   └── Auth/      # Fortify auth controllers
│   ├── Livewire/      # Legacy Livewire components (prefer app/Livewire/)
│   ├── Middleware/     # HTTP middleware
│   └── Requests/      # Form request classes
├── Jobs/              # Queued jobs
├── Listeners/         # Event listeners
├── Livewire/          # Livewire components (main location)
├── Models/            # Eloquent + Orbit models
├── Notifications/     # Email/notification classes
├── Policies/          # Authorization policies
├── Properties/        # Column name abstraction traits ({Model}Entity)
├── Providers/         # Service providers
├── Services/          # Business logic services

config/                # Configuration files
database/
├── factories/         # Model factories
├── migrations/        # Database migrations
└── seeders/           # Database seeders

function/
└── Global.php         # Global helper functions + constants

resources/views/
├── components/        # Blade components
│   ├── cms/           # CMS-specific components
│   ├── form/          # Form components
│   └── warehouse/     # WMS-specific components
├── frontend/          # Public-facing views
│   ├── layouts/
│   └── sections/      # Dynamic content sections
├── layouts/           # Admin/auth layouts
├── livewire/          # Livewire component views
├── pages/             # CRUD page views (pages/{module}/{action})
│   └── {module}/      # One folder per module
│       └── partials/  # Module-specific partials
└── pdf/               # PDF templates

routes/
├── web.php            # Main web routes
├── api.php            # Sanctum API routes
└── settings.php       # Profile/settings routes
```

---

## Model Pattern

### Standard Database Model

```php
<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use App\Concerns\DefaultEntity;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['name', 'email', 'role', 'avatar'])]
class Product extends BaseModel
{
    use DefaultEntity, Filterable, Sortable;

    protected $table = 'product';
    protected $primaryKey = 'product_id';

    // Filterable & sortable columns (for laravel-purity)
    public static $filterColumns = ['product_nama'];
    public static $sortColumns = ['product_nama', 'product_id_satuan'];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    // REQUIRED: display name field for UI
    public static function field_name(): string
    {
        return 'product_nama';
    }

    // REQUIRED: validation rules
    public function rules(): array
    {
        return [
            'product_nama' => 'required|string',
        ];
    }

    // Accessor for file URL (delegates to fileUrl() helper in Global.php)
    public function getAvatarUrlAttribute(): string
    {
        return fileUrl($this->avatar);
    }

    // Lifecycle hooks
    protected static function booted(): void
    {
        static::created(function (self $model) { /* ... */ });
    }

    // Relationships (always prefix with "has" so it's obvious: hasProducts, hasCategory)
    public function hasSatuan()
    {
        return $this->hasOne(Satuan::class, 'satuan_id', 'product_id_satuan');
    }

    // Usage: $category->has_satuan (Laravel magic property)
}
```

**Key Model Details:**
- Use `#[Fillable([...])]` attribute (PHP 8 attribute style, preferred in this project) rather than `protected $fillable`.
- Casts go in `protected function casts(): array` (modern Laravel style).
- For fields that store file paths (e.g. `'avatar'`), add a `get{Field}UrlAttribute()` accessor that resolves the path to a public URL — follow `WebsiteSetting::fileUrl()` for consistency.
- File fields in `rules()` use `'nullable|string|max:255'` (the actual file upload is handled by the controller; the action pipeline only sees the resolved path string).
- Relationship methods use the `has` prefix — **all** relations (hasMany, hasOne, belongsTo, belongsToMany) must start with `has`, e.g. `hasProducts()`, `hasCategory()`. This makes them immediately distinguishable from regular methods. When accessed as a dynamic property via Laravel, it reads `$product->has_category->field_name`.
- Use `OptionTrait` on models — provides `getOptions()` that plucks by `field_name()` / primary key automatically, avoiding hardcoded column names: `Category::getOptions()` instead of `Category::pluck('name', 'id')`.

### Property Traits — Column Name Abstraction

When a model has columns that need to be referenced by name in controllers, views, or Blade, create a **Property trait** in `app/Properties/{Model}Entity.php`. This provides a single source of truth for column names:

```php
<?php

namespace App\Properties;

trait UserEntity
{
    // Static method: User::field_email() → 'email'
    public static function field_email()
    {
        return 'email';
    }

    // Accessor: $user->field_email → 'value'
    public function getFieldEmailAttribute()
    {
        return $this->{static::field_email()};
    }
}
```

Then use it in the model:

```php
use App\Properties\UserEntity;

class User extends Authenticatable
{
    use UserEntity;
}
```

**Convention:**
- File: `app/Properties/{Model}Entity.php` (singular model name + `Entity` suffix)
- Static method: `field_{table_column}` (snake_case)
- Accessor: `getField{ColumnName}Attribute()` (PascalCase after `getField`)
- Usage: `User::field_email()` or `$user->field_email`
- Naming: always use module_field (example for module product), product_id, product_nama, product_id_satuan,

### Orbit (Flat-File JSON) Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Schema\Blueprint;
use Orbit\Concerns\Orbital;

class Example extends BaseModel
{
    use Orbital;

    protected $fillable = ['name', 'slug', 'data'];

    protected $casts = [
        'data' => 'array',
    ];

    public static $sortColumns = ['name'];
    public static $filterColumns = ['name'];

    public static function field_name(): string
    {
        return 'name';
    }

    // REQUIRED: define database schema
    public static function schema(Blueprint $table): void
    {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->json('data')->nullable();
    }

    // REQUIRED: return 'json' for flat-file
    public static function getOrbitalDriver(): string
    {
        return 'json';
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
        ];
    }
}
```

**Important:** Orbit models CANNOT use `belongsToMany` — they use separate SQLite databases so pivot tables won't cross-connect. Store category/tag IDs as JSON arrays instead.

## Naming Conventions

| Item | Convention | Example |
|---|---|---|
| **Model** | `PascalCase` singular | `Content.php` → `class Content` |
| **Controller** | `PascalCase` + `Controller` suffix | `ProductController.php` |
| **Livewire** | `PascalCase` matching filename | `FlexibleContainer.php` |
| **Policy** | `PascalCase` + `Policy` suffix | `ContentPolicy.php` |
| **Service** | `PascalCase` + `Service` suffix | `CentrifugoService.php` |
| **Trait/Concern** | `PascalCase` + `Trait` suffix | `ControllerTrait.php` |
| **Enum** | `PascalCase` + `Enum` suffix | `StatusEnum.php` |
| **Event** | `PascalCase` past-tense verb | `NotificationSent.php` |
| **Job** | `PascalCase` descriptive | `SendTelegramContentJob.php` |
| **Listener** | `PascalCase` descriptive | `SendNotificationViaCentrifugo.php` |
| **Request** | `PascalCase` + `Request` suffix | `GeneralRequest.php` |
| **Migration** | Laravel default timestamp prefix | `2025_01_01_000000_create_xxx.php` |

### Database Tables
- Legacy WMS: `snake_case` singular (e.g., `product`, `stock`, `po`)
- New tables: Laravel convention `snake_case` plural recommended

### View Templates
- **Page views:** `pages/{module}/{action}.blade.php`
  - Module = controller name without "Controller", lowercase
  - Action: `table`, `form` (for create/update), `show`, custom
- **Components:** `components/{group}/{name}.blade.php`
- **Livewire views:** `livewire/{component-name}.blade.php`
- **Frontend:** `frontend/{page}.blade.php`

### Routes
- Named routes: `{prefix}-{module}.{action}` (e.g., `wms-product.table`)
- Use `Route::auto()` macro for standard CRUD routing
---

## Controller Pattern

### Minimal Controller (uses trait defaults)

```php
<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Supplier;

class SupplierController extends Controller
{
    use ControllerTrait;

    public function __construct(Supplier $model)
    {
        $this->model = $model::getModel();
    }
}
```

### Full Controller with Custom Logic

```php
<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ControllerTrait;

    public function __construct(Product $model)
    {
        $this->model = $model::getModel();
    }

    // Override share() to add view data
    protected function share($data = [])
    {
        return array_merge([
            'model'          => $this->model,
            'categoryOptions' => Category::getOptions(),   // uses OptionTrait → [id => name]
        ], $data);
    }

    // Override getData() for custom queries — use leftJoinRelationship, not with()
    protected function getData()
    {
        return $this->model->leftJoinRelationship('has_stock')->filter()->sort();
    }

    // Custom action method
    public function getCustomAction(Request $request, int $id)
    {
        $record = $this->model->findOrFail($id);

        return $this->views('pages.product.custom', [
            'model' => $record,
        ]);
    }
}
```

### Key Controller Conventions

- **Always use `$model::getModel()`** to ensure a fresh instance
- **`share($data)`**: add data available to all views; always call `array_merge()` with defaults
- **`getData()`**: customize the base query for `getTable` (eager loading, scopes)
- **`views($view, $data)`**: render view or JSON based on request type
- **`template()`**: auto-resolves view path as `pages.{module}.{action}` where module = controller name minus "Controller"
- **`response($payload)`**: send JSON for API, flash+redirect for web
- **Trait aliasing** — when overriding `postCreate`/`postUpdate` to add file-upload logic, use trait aliasing so the original trait method can still be called:
  ```php
  use ControllerTrait {
      postCreate as traitPostCreate;
      postUpdate as traitPostUpdate;
  }
  // Then call $this->traitPostCreate($request) / $this->traitPostUpdate($request, $id)
  ```

### File/Image Upload in Controllers

When a form includes a file upload field, handle it **before** the trait action:

```php
use App\Http\Requests\GeneralRequest;

class UsersController extends Controller
{
    use ControllerTrait {
        postCreate as traitPostCreate;
        postUpdate as traitPostUpdate;
    }

    public function postCreate(GeneralRequest $request)
    {
        $avatar = $this->handleAvatar($request, null);
        if ($avatar !== null) {
            $request->merge(['avatar' => $avatar]);
        }

        return $this->traitPostCreate($request);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $user = $this->model->findOrFail($id);
        $existing = $user->avatar ?? null;

        $avatar = $this->handleAvatar($request, $existing);
        if ($avatar !== $existing) {
            $request->merge(['avatar' => $avatar]);
        }

        return $this->traitPostUpdate($request, $id);
    }

    private function handleAvatar(GeneralRequest $request, ?string $existing): ?string
    {
        if ($request->hasFile('avatar')) {
            try {
                $path = uploadFile($request->file('avatar'), 'users', ['max_size' => 2048]);
                $this->deleteUserFile($existing);

                return $path;
            } catch (\InvalidArgumentException $e) {
                throw ValidationException::withMessages(['avatar' => $e->getMessage()]);
            }
        }

        // Remove checkbox
        if ($request->boolean('remove_avatar')) {
            $this->deleteUserFile($existing);

            return null;
        }

        return $existing; // unchanged
    }

    private function deleteUserFile(?string $path): void
    {
        if (empty($path)) return;
        $file = storage_path('app/public/'.$path);
        if (file_exists($file)) { unlink($file); }
    }
}
```

**Upload pattern** (uses `uploadFile()` helper from `function/Global.php`):
- `uploadFile($file, 'folder', ['max_size' => 2048])` handles validation, MIME check, EXIF stripping, sanitization
- Files stored to `storage/app/public/{folder}/` → accessible via `/storage/{folder}/xxx.jpg`
- Validate the raw file inside `uploadFile()` — catch `InvalidArgumentException` and re-throw as `ValidationException` for inline errors
- After uploading, merge the **resolved path string** (e.g. `'users/abc.jpg'`) into the request
- The CreateAction/UpdateAction then validates the path as `'nullable|string|max:255'` (via model rules) and mass-assigns it normally
- For "remove" support, the form includes a checkbox `remove_{field}`; the controller checks `$request->boolean('remove_{field}')` and sets the field to null
- Delete uses `storage_path('app/public/'.$path)` — consistent with `uploadFile()` storage location

### Controller Action Methods (from ControllerTrait)

| Method | Route Action | Purpose |
|---|---|---|
| `index()` | `index` | Redirects to `getTable` |
| `getTable()` | `table` | Paginated data table |
| `getCreate()` | `form` | Create form |
| `postCreate()` | `form` (POST) | Handle create |
| `getUpdate($id)` | `form` | Edit form |
| `postUpdate($id)` | `form` (POST) | Handle update |
| `getDelete($id)` | `delete` | Single delete |
| `postDelete()` | `delete` (POST) | Bulk delete |
| `getShow($id)` | `show` | Show record (API-normally) |

---

## Policy Pattern

**CRITICAL:** Every model MUST have a corresponding Policy. Without it, all requests will return 403 Forbidden because `GeneralRequest::authorize()` checks `$user->can($action, $model)` against the policy.

All policies extend `BasePolicy` — extremely minimal:

```php
<?php

namespace App\Policies;

class ProductPolicy extends BasePolicy {}
```

`BasePolicy` reads `config('permision.php')` which maps roles to allowed actions per module.

**Rule:** If you create a model, create its policy. One policy per model, in `app/Policies/{Model}Policy.php`.

---

## Enum Pattern

Use **`bensampo/laravel-enum`** (class extending `BenSampo\Enum\Enum`) with `EnumTrait`:

```php
<?php

namespace App\Enums;

use App\Concerns\EnumTrait;
use BenSampo\Enum\Enum;

final class StatusEnum extends Enum
{
    use EnumTrait;

    const PENDING  = 'pending';
    const APPROVED = 'approved';

    public static function getDescription(mixed $value): string
    {
        return match ($value) {
            self::PENDING  => 'Pending',
            self::APPROVED => 'Approved',
            default => parent::getDescription($value),
        };
    }
}
```

### Enum Folder Structure

```
app/Enums/
├── RoleEnum.php            # App-wide enums
├── StatusEnum.php
├── Wms/                    # WMS domain enums
│   ├── MasukStatusEnum.php
│   ├── PoStatusEnum.php
│   └── SoStatusEnum.php
└── Telegram/               # Telegram domain enums
    └── TelegramContentType.php
```

### Key Conventions

- All enums extend `BenSampo\Enum\Enum` (not native PHP enums)
- All enums use `App\Concerns\EnumTrait` for `getOptions()` and `getApi()`
- Constants define key→value pairs: `const KEY = 'value';`
- `getDescription(mixed $value): string` — static, uses `match($value)` not `$this`
- Domain-specific enums go in subdirectories: `App\Enums\Wms\`, `App\Enums\Telegram\`

### EnumTrait — getOptions() & getApi()

These two convenience methods are provided by `App\Concerns\EnumTrait` and build on top of bensampo's `asSelectArray()` (which returns `[value => description]`):

- **`getOptions(mixed $value = null): array`**
  - No argument → returns all `[value => description]` pairs.
  - Accepts a **bensampo enum instance**, a raw **value**, or an enum **key** (`'ADMIN'`).
  - Accepts an **array** of any mix above — **preserves the requested order** in the result.
  - Unknown values are silently skipped; use for `<select>` inputs.
  - `getOptions()` → `['pending' => 'Pending', 'approved' => 'Approved']`

- **`getApi(mixed $value = null): array`** — same selection rules, but returns `[['id' => value, 'name' => description], ...]` format for JSON APIs.

The trait also has a `resolveEnumSelection(mixed $item)` helper that normalizes any selection into a raw value (or null when not part of the enum).

### Built-in bensampo methods

`getKeys()`, `getValues()`, `asArray()`, `asSelectArray()`, `fromValue()`, `coerce()`, `getRandomInstance()`, `hasKey()`, `hasValue()`, `getValue()`, `getKey()`.

---

## Service Pattern

**For business logic, prefer Laravel Actions** (`lorisleiva/laravel-actions`) placed in `app/Actions/`. Extend from `Lorisleiva\Actions\Action` and use the `AsAction` trait.

Plain service classes (constructor injection, `app()` helper) are still used for cross-cutting concerns (e.g. `CentrifugoService`, `NotificationChannelFactory`), but CRUD operations and reusable business logic belong in Actions.

```php
<?php

namespace App\Actions;

use App\Concerns\PayloadTrait;
use App\Concerns\RulesTrait;
use Illuminate\Http\Request;
use Lorisleiva\Actions\Concerns\AsAction;

class MyAction
{
    use AsAction, PayloadTrait, RulesTrait;

    public function rules(): array
    {
        return $this->mergeRules($this->model);
    }

    public function handle(Request $request, $model)
    {
        $this->model = $model;
        $data = $request->validate($this->rules());

        // Business logic here

        return $this->payload(TOAST_SUCCESS, $data);
    }
}

// Usage:
$response = MyAction::run($request, $this->model);
```

---

## Blade Form Components Reference

The project uses custom Blade components for all form pages. These are in `resources/views/components/`.

### Layout Components

| Component | Purpose | Key Props |
|---|---|---|
| `<x-layouts::app>` | Page wrapper with sidebar + topbar | `:title` |
| `<x-breadcrumb>` | Breadcrumb navigation | `:items="[['url'=>'...','label'=>'...'], ...]"` |
| `<x-card>` | Card wrapper (with optional header + 12-col grid) | `:label`, `:icon`, `:noGrid` |
| `<x-form>` | Form element (auto CSRF, method spoofing) | `:model`, `:action`, `:method` |

### Form Input Components

All form inputs require at minimum `name` and support `col` (grid span, 1–12), `label` (auto-formatted from name if omitted), and show inline validation errors.

| Component | Purpose | Extra Props |
|---|---|---|
| `<x-input>` | Text input | `type` (text/password/email/date/…), `:model` |
| `<x-select>` | Select dropdown | `:options` (assoc array), `:multiple`, `class="search"` enables Tom Select |
| `<x-textarea>` | Textarea | — |
| `<x-checkbox>` | Single checkbox | `:value`, `:checked` |
| `<x-radio>` | Radio group | `:options` |
| `<x-toggle>` | Toggle switch | — |
| `<x-date>` | Date picker | — |
| `<x-file>` | File/image upload | `accept`, `capture`, `:preview`, `:value`, `:helper` |

### `<x-file>` — Image Upload with Camera

The file component supports two modes:

1. **Standard mode** (`:preview="false"`, default) — dashed dropzone with "upload_file" icon.
2. **Image preview mode** (`:preview="true"`) — round image preview + "Pilih / Ambil Foto" button + remove checkbox.

```blade
<x-file
    name="avatar"
    label="Foto Profil"
    col="12"
    accept="image/*"
    capture="environment"        {{-- forces camera on mobile --}}
    :preview="true"
    :value="$model?->avatar_url" {{-- existing file URL for preview --}}
    helper="Ambil foto via kamera di HP atau pilih dari galeri" />
```

| Prop | Type | Default | Description |
|---|---|---|---|
| `name` | string | *required* | Field name |
| `label` | string|null | auto | Label text |
| `col` | string | `12` | Grid span |
| `accept` | string | `image/*` | File type filter |
| `capture` | string|null | null | HTML capture attribute (`"environment"` = back camera) |
| `preview` | bool | false | Show round preview + button + remove checkbox |
| `value` | string|null | null | Existing file URL for preview |
| `helper` | string|null | null | Helper text below |
| `multiple` | bool | false | Allow multiple files |

### Form Action Component

```blade
<x-action :model="$model" :action="['save']" :cancel="url()->previous()" />
```

Provides sticky bottom bar with Create/Save/Update/Delete/Cancel buttons. The `action` array controls which buttons are visible.

### Form Pattern Example

```blade
<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="name" />
                <x-input col="6" name="email" />
                <x-input col="6" type="password" name="password" />
                <x-select col="6" name="role" :options="$role" />

                <x-file name="avatar" col="12" accept="image/*" capture="environment"
                    :preview="true" :value="$model?->avatar_url"
                    helper="Ambil foto via kamera di HP atau pilih dari galeri" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
```

**Form conventions:**
- Always add `enctype="multipart/form-data"` to `<x-form>` when any file input is present.
- Use `@bind($model ?? null)` around inputs so bound values populate correctly.
- Inputs wrap in `<x-card>` which provides a 12-column CSS grid — use `col="6"` for 2-column layout.
- Dropdown options come from `share()` as Enum `getOptions()`: `RoleEnum::getOptions()`.

---

## Livewire Component Pattern

```php
<?php

namespace App\Livewire\Cms;

use Livewire\Component;
use App\Models\SomeModel;

class ExampleComponent extends Component
{
    public int $id;
    public string $name = '';
    public array $items = [];

    protected $listeners = ['refresh' => '$refresh'];

    public function mount(int $id)
    {
        $this->id = $id;
        // Initialize state
    }

    public function someAction()
    {
        // Handle action
    }

    public function render()
    {
        return view('livewire.cms.example-component', [
            'data' => SomeModel::find($this->id),
        ]);
    }
}
```

- Components in `app/Livewire/{Group}/`
- Views in `resources/views/livewire/{group}/{component-name}.blade.php`
- Public properties are reactive; use `$listeners` for events

---

## Laravel Action Pattern (lorisleiva)

```php
// Usage in controllers:
$response = CreateAction::run($request, $this->model);
$response = UpdateAction::run($request, $id, $this->model);
$response = DeleteAction::run($request, $this->model);
```

Actions return a payload array:
```php
[
    'code'    => 200,        // HTTP status
    'status'  => true,       // success/fail
    'message' => '...',      // TOAST_SUCCESS or TOAST_FAILED
    'data'    => $result,     // the model or error message
]
```

When creating new actions: extend from `Lorisleiva\Actions\Action`, use `AsAction` trait.
---

## Route Registration

**CRITICAL:** Always use `izniburak/laravel-auto-routes` for routing. Do **not** manually register routes for standard CRUD controllers. The `Route::auto()` macro automatically generates all endpoints from the controller's action methods.

### Standard CRUD — auto-routes only

```php
// In routes/web.php inside the auth middleware group:

Route::auto('/users', 'UsersController', ['name' => 'users']);
// Auto-generates:
//   GET  /users           → getTable
//   GET  /users/create    → getCreate
//   POST /users/create    → postCreate
//   GET  /users/update/{id} → getUpdate
//   POST /users/update/{id} → postUpdate
//   GET  /users/delete/{id} → getDelete
//   POST /users/delete    → postDelete (bulk)
//   GET  /users/show/{id} → getShow
```

**Rule:** If a controller uses `ControllerTrait`, it gets `Route::auto()`. Never write `Route::get()` / `Route::post()` for standard CRUD.

### Custom Routes (edge cases only)

Only add manual routes for non-standard actions that don't follow the CRUD pattern — e.g. QR code, PDF export, custom AJAX endpoints:

```php
Route::get('/users/{id}/qrcode', [UsersController::class, 'getQrcode'])->name('users.getQrcode');
Route::post('/users/{id}/qrcode', [UsersController::class, 'postQrcode'])->name('users.postQrcode');
```

### Livewire Routes

```php
Route::livewire('settings/appearance', 'pages::settings.appearance')
    ->name('appearance.edit');
```

### Frontend Public Routes

```php
Route::get('/{slug}', [PublicController::class, 'page'])->name('page');
```

---

## Request/Validation Pattern

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneralRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Reads model from controller and checks policy
        $controller = request()->route()->getController();
        $model = $controller->model ?? null;
        if ($model === null) return true;

        $action = str_replace(['get', 'post'], '', strtolower(
            request()->route()->getActionMethod()
        ));
        $action = $action === 'index' ? 'table' : $action;

        return $this->user()->can($action, $model);
    }

    public function rules(): array
    {
        return [];
    }
}
```

- Model validation rules are defined in the model's `rules()` method
- Controller uses `$request->validate($this->model->rules())` or the Laravel Action handles it

---

## Global Helpers & Constants

Defined in `function/Global.php` (autoloaded via `composer.json` `files`):

```php
// Constants
define('ACTION_CREATE', 'getCreate');
define('ACTION_UPDATE', 'getUpdate');
define('TOAST_SUCCESS', 'Data berhasil di proses !');
define('TOAST_FAILED', 'Proses Error !');

// Key functions
formatDate($value, $datetime = false)   // Format date d/m/Y
formatAngka(int $value, $simbol = null) // Format number with thousand separator
formatQty($value)                       // Smart decimal formatting
modules($action = null)                  // Current route module name
moduleLabel()                           // Human-readable module label from menu config
unicString($length)                    // Random uppercase string
unicNumber($length)                    // Random integer
renderFieldInput($fName, $fType, ...)   // Render form input HTML for custom fields
```

### File Upload Helpers

```php
// Resolve stored file path → public URL. Handles empty, absolute URLs,
// uploadFile() paths ("users/abc.jpg" → "/storage/users/abc.jpg"),
// and legacy "storage/" prefixed paths.
fileUrl(?string $path): string

// Render <img> tag from stored path. Returns "" if src is empty.
// Auto-resolves URL via fileUrl(). Supports class, id, style attributes.
renderImage(?string $src, string $alt = '', string $class = '', string $id = '', string $style = ''): string

// Upload an image file with validation, MIME check, EXIF stripping, sanitization.
// Returns relative path like "avatars/abc123.jpg" (stored in storage/app/public/).
uploadFile($file, string $folder = 'uploads', array $options = []): string
// Options: max_size (KB, default 2048), max_width, max_height

// Sanitize filename: strip path traversal, null bytes, PHP wrappers.
// Returns random unique safe filename like "aB3xK_20260819.jpg".
sanitizeFileName(string $originalName): string
```

---

## Events & Listeners Pattern

```php
// Event
<?php
namespace App\Events;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockCreated
{
    use Dispatchable, SerializesModels;
    public function __construct(public $stock) {}
}

// Listener
<?php
namespace App\Listeners;

class LogStockActivity
{
    public function handle(StockCreated $event): void { /* ... */ }
}

// Register in AppServiceProvider::boot():
Event::listen(NotificationSent::class, SendNotificationViaCentrifugo::class);
```

---

## Notification Service Pattern

Notifications use a Channel Factory pattern in `app/Services/Notification/`:

```php
$channel = NotificationChannelFactory::create($type); // 'email', 'telegram', 'whatsapp', 'log'
$channel->send($recipient, $subject, $body);
```

Available channels: `EmailChannel`, `TelegramChannel`, `WhatsAppChannel` (with multiple providers: `FonnteProvider`, `TwilioProvider`, `CustomProvider`, `LogProvider`), `LogChannel`.
---

## File Creation Checklist

When creating a new feature/module, follow these steps:

1. **[ ] Enum** (if needed) — `app/Enums/NameEnum.php` extending `BenSampo\Enum\Enum`
   - Use `EnumTrait` trait
   - Define constants: `const KEY = 'value';`
   - Override `getDescription(mixed $value): string` using `match($value)`
   - For dropdowns use `NameEnum::getOptions()` in controller's `share()`

2. **[ ] Model** — `app/Models/Name.php` extending `BaseModel` (or with `Orbital` trait for flat-file)
   - Use `#[Fillable([...])]` attribute (preferred) or `protected $fillable`
   - Casts in `protected function casts(): array`
   - Define `$filterColumns`, `$sortColumns` static properties
   - Define `field_name()` static method
   - Define `rules()` method for validation
   - Define relationships
   - Define `static function schema(Blueprint $table)` if using Orbit
   - For file fields: add `get{Field}UrlAttribute()` accessor + include as `'nullable|string|max:255'` in rules

3. **[ ] Migration** — `database/migrations/YYYY_MM_DD_create_table_name.php` (if database model)

4. **[ ] Policy (REQUIRED)** — `app/Policies/NamePolicy.php` extending `BasePolicy`
   - Without it, all requests 403 Forbidden!

5. **[ ] Controller** — `app/Http/Controllers/NameController.php`
   - Use `ControllerTrait`
   - Set `$this->model` in constructor
   - Override `share()` if extra view data needed (e.g. enum options)
   - Override `getData()` if custom query needed
   - If file upload needed: use trait aliasing + `handle{Field}()` helper (see File/Image Upload section)

6. **[ ] Route** — Add `Route::auto()` in `routes/web.php`

7. **[ ] Views** — Create in `resources/views/pages/{module}/`:
   - `table.blade.php` — data table view (use `<x-table>`, `<x-table-action>`, `<x-table-checkbox>`, `<x-pagination>`)
   - `form.blade.php` — create/edit form (use `<x-form>`, `<x-card>`, `<x-input>`, `<x-select>`, `<x-file>`, `<x-action>`)
   - Always add `enctype="multipart/form-data"` to `<x-form>` when a file input is present

8. **[ ] Permission** — Update `config/permision.php` if role restrictions needed

9. **[ ] Menu** — Update `config/menu.php` to add navigation item

10. **[ ] Tests** — Pest tests in `tests/`

---

## Important Project-Specific Details

### Laravel Boost / Zoo Code Agent
- The project registers a custom `zoo_code` agent via `App\Boost\Agents\CustomAgent`
- Guidelines file: `AGENTS.md` (this file)
- MCP config: `.roo/mcp.json`
- Skills directory: `.agents/skills/`

### Orbit Models (Flat-file CMS)
- Content types: `Type`, `Section`, `Field` are Orbit models
- Content entries: `Content` is also Orbit (but can be mixed)
- These use `.json` files in `content/` directory
- **No belongsToMany** relationships — store references as JSON arrays

### Model Aliases
- `ModelAliasServiceProvider` auto-creates global class aliases for all models
- Example: `use App\Models\Product` → can also be referenced as just `Product`

### Permission System
- Config file: `config/permision.php`
- `BasePolicy` checks `$restrict[$role][$module]` array for allowed actions
- `GeneralRequest::authorize()` calls `$user->can($action, $model)`

### Multi-language
- Lang files in `lang/en/` and `lang/id/`
- Indonesian is primary language

### NativePHP Mobile
- Mobile app support via `nativephp/mobile`
- Routes defined in `routes/web.php` with mobile-specific views
---

## Command Reference

```bash
# Composer — always install without dev dependencies
composer install --no-dev   # Production (default)
composer install            # Only if you need testing/linting tools

# After every composer install, activate Laravel Boost
php artisan boost:update

# Development
composer dev              # Start server + queue + vite concurrently

# Testing
composer test             # Run tests with lint check
php artisan test          # Run Pest tests only
php artisan dusk          # Browser tests

# Linting
composer lint             # Fix linting with Pint
composer lint:check       # Check linting only

# Code Generation
php artisan make:model    # Create model
php artisan make:livewire # Create Livewire component
php artisan boost:update  # Update Laravel Boost
```

---

## Code Style Notes

- PHP uses 4-space indentation, no tabs
- String literals use double quotes (preferred in this project)
- Array syntax: `[]` (short array syntax)
- Type hints: use PHP 8.3 type declarations everywhere
- Return types: always declare return types (`: void`, `: string`, `: array`)
- `declare(strict_types=1)` only where explicitly needed
- Comments: `//` for single-line, `/** */` for docblocks
- PSR-4 autoloading: `App\` → `app/`, `Function\` → `function/`, `Tests\` → `tests/`
- Some files have `// ponytail:` comments — these are code review notes from a previous AI agent; include similar context notes when making non-obvious decisions
