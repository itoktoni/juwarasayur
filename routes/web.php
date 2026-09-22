<?php

use App\Http\Controllers\CrmDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrepareController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\WebsiteSettingController;
use App\Models\Notification;
use App\Services\CentrifugoService;
use Buki\AutoRoute\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Ecommerce\Http\Controllers\HomeController;
use Modules\Ecommerce\Http\Controllers\StorefrontController;

// Favicon dinamis dari Settings → Website (hilangkan 404 GET /favicon.ico di console & globe di auth pages)
Route::get('/favicon.ico', function () {
    $raw = \App\Models\WebsiteSetting::merged()['favicon'] ?? null;
    $url = \App\Models\WebsiteSetting::fileUrl($raw);
    $path = $url ? public_path(ltrim($url, '/')) : null;
    if ($path && is_file($path)) {
        return response()->file($path, ['Cache-Control' => 'public, max-age=31536000']);
    }
    // fallback: file di public/storage/website/ terbaru jika config belum kebaca
    $fallback = public_path('storage/website/6aa575ef4f3f4_favicon.png');
    if (is_file($fallback)) {
        return response()->file($fallback, ['Cache-Control' => 'public, max-age=31536000']);
    }
    abort(404);
});

// Halaman khusus pendaftaran reseller & affiliator (POST tetap ditangani Fortify: register.store)
Route::middleware('guest')->get('/register/reseller', fn () => view('pages::auth.register-reseller'))
    ->name('register.reseller');
Route::middleware('guest')->get('/register/affiliator', fn () => view('pages::auth.register-affiliator'))
    ->name('register.affiliator');

Route::middleware('auth')->post('/centrifugo/token', function (Request $request) {
    if (! config('centrifugo.enabled')) {
        return response()->json(['token' => 'disabled']);
    }

    $centrifugo = app(CentrifugoService::class);
    $user = Auth::user();

    if ($request->input('channel')) {
        return response()->json([
            'token' => $centrifugo->generateSubscriptionToken((string) $user->id, $request->input('channel')),
        ]);
    }

    return response()->json([
        'token' => $centrifugo->generateConnectionToken((string) $user->id),
    ]);
});

// Download harga produk (semua role yang login)
Route::middleware(['auth', 'verified'])->get('/dashboard/download-prices', [DashboardController::class, 'downloadPrices'])->name('dashboard.download-prices');
// Download khusus harga reseller (grosir) — semua role yang login
Route::middleware(['auth', 'verified'])->get('/dashboard/download-reseller-prices', [DashboardController::class, 'downloadResellerPrices'])->name('dashboard.download-reseller-prices');

// Halaman admin: prefix /admin, diblokir untuk user tipe customer & reseller
Route::prefix('admin')->middleware(['auth', 'verified', 'access', 'admin'])->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('crm/dashboard', CrmDashboardController::class)->name('crm.dashboard');
    Route::get('crm/referral', \App\Http\Controllers\CrmReferralController::class)->name('crm.referral');

    Route::auto('/user', 'UsersController', ['name' => 'user']);

    Route::get('/native-bridge-test', function () {
        return view('pages.settings.native-bridge-test');
    })->name('native-bridge-test');

    Route::get('/settings/website', [WebsiteSettingController::class, 'index'])->name('settings.website');
    Route::post('/settings/website', [WebsiteSettingController::class, 'save'])->name('settings.website.save');

    Route::auto('/shipping', ShippingController::class, ['name' => 'shipping']);
    Route::auto('/withdrawal', 'WithdrawalController', ['name' => 'withdrawal']);

    // Prepare dari SO (barang keluar gudang) — modul ringan
    Route::prefix('prepare')->name('prepare.')->controller(PrepareController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::match(['get', 'post'], '/group', 'group')->name('group');
        Route::post('/prepare-all', 'storePrepareAll')->name('prepareAll');
        Route::get('/{product}/prepare', 'prepareForm')->name('prepareForm');
        Route::post('/{product}/prepare', 'storePrepare')->name('storePrepare');
        Route::get('/progress', 'progress')->name('progress');
        Route::get('/print-label', 'printLabel')->name('printLabel');
    });

    Route::prefix('notifications-web')->group(function () {
        Route::get('/', function (Request $request) {
            $notifications = Notification::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->limit($request->input('limit', 50))
                ->get();

            $unreadCount = Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count();

            return response()->json([
                'notifications' => $notifications->map(fn ($n) => [
                    'id' => $n->id,
                    'icon' => $n->icon,
                    'iconColor' => $n->icon_color,
                    'title' => $n->title,
                    'body' => $n->body,
                    'url' => $n->url,
                    'type' => $n->type,
                    'read' => $n->read,
                    'time' => $n->created_at?->diffForHumans() ?? '',
                    'created_at' => $n->created_at->toIso8601String(),
                ]),
                'unread_count' => $unreadCount,
            ]);
        });

        Route::put('/{id}/read', function (int $id) {
            $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
            $notification->update(['read' => true]);

            return response()->json(['message' => 'Marked as read']);
        });

        Route::put('/read-all', function () {
            Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->update(['read' => true]);

            return response()->json(['message' => 'All marked as read']);
        });
    });
});

// Referral short link: /r/CODE → set cookie+session via middleware lalu redirect home
Route::get('/r/{code}', function (string $code) {
    // Middleware CaptureAffiliateRef sudah set cookie+session; tinggal redirect
    return redirect()->route('home');
})->name('referral.redirect');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/product', [StorefrontController::class, 'index'])->name('shop.index');
Route::get('/product/{slug}', [StorefrontController::class, 'show'])->name('shop.show');

require __DIR__.'/settings.php';
