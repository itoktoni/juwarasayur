<?php

namespace Modules\Ecommerce\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\So\Models\So;

/**
 * Controller publik untuk area akun (profile, customer affiliator).
 * Diakses dari halaman depan tanpa middleware 'admin' — modul-modul
 * CRUD lain adalah area admin dan tidak boleh dibuka affiliator/customer.
 */
class AccountController extends Controller
{
    // ------------------------------------------------------------------
    // Dashboard (khusus affiliator)
    // ------------------------------------------------------------------
    public function dashboard(): View
    {
        $this->authorizeAffiliator();

        $user = Auth::user();
        $today = now()->today();

        $baseSo = fn () => So::where('so_id_reseller', $user->id);

        $stats = [
            'orders_today' => (clone $baseSo())->whereDate('so_tanggal', $today)->count(),
            'revenue_today' => (float) (clone $baseSo())
                ->whereDate('so_tanggal', $today)
                ->whereNot('so_status', \Modules\So\Enums\SoStatusEnum::CANCELLED)
                ->sum('so_grand_total'),
            'unpaid' => (clone $baseSo())->where('so_status', \Modules\So\Enums\SoStatusEnum::PENDING)->count(),
            'to_prepare' => (clone $baseSo())
                ->whereIn('so_status', [\Modules\So\Enums\SoStatusEnum::PAID, \Modules\So\Enums\SoStatusEnum::CONFIRMED])
                ->count(),
            'total_orders' => (clone $baseSo())->count(),
            'revenue_total' => (float) (clone $baseSo())
                ->whereNot('so_status', \Modules\So\Enums\SoStatusEnum::CANCELLED)
                ->sum('so_grand_total'),
            'customers' => User::where('type', UserTypeEnum::CUSTOMER)
                ->where('reference_id', $user->id)->count(),
        ];

        // Tren penjualan 7 hari terakhir untuk bar chart CSS
        $dailySales = collect(range(6, 0))->map(function (int $i) use ($baseSo) {
            $day = now()->today()->subDays($i);

            return [
                'label' => $day->translatedFormat('D'),
                'total' => (float) (clone $baseSo())
                    ->whereDate('so_tanggal', $day)
                    ->whereNot('so_status', \Modules\So\Enums\SoStatusEnum::CANCELLED)
                    ->sum('so_grand_total'),
            ];
        });

        $recentOrders = (clone $baseSo)()
            ->with(['has_customer'])
            ->orderByDesc('so_tanggal')
            ->limit(6)
            ->get();

        // Rekap fee_snapshot per order affiliator — sumber transparansi komisi
        // (siapa affiliator bisa cek fee_percent/fee_amount tiap baris order).
        $recentOrders->load(['has_details.has_product']);

        // Payload JSON untuk modal pop-up "Detail fee" — dihitung di sini agar
        // view cukup json_encode tanpa struktur arrow-fn yang bikin Blade bingung.
        $feePopupData = $recentOrders->mapWithKeys(fn ($o) => [
            (string) $o->id => [
                'code' => (string) $o->so_code,
                'total' => (float) $o->has_details->sum('fee_amount'),
                'rows' => $o->has_details->map(fn ($d) => [
                    'name' => (string) ($d->has_product?->product_nama ?? $d->so_detail_code),
                    'qty' => (int) $d->so_detail_qty,
                    'pct' => (float) $d->fee_percent,
                    'amount' => (float) $d->fee_amount,
                ])->all(),
            ],
        ])->all();

        return view('ecommerce::pages.account.dashboard', [
            'stats' => $stats,
            'dailySales' => $dailySales,
            'feePopupData' => $feePopupData,
            'recentOrders' => $recentOrders,
            'salesChart' => $this->omzetBarChart($dailySales),
            // Komisi affiliator (fee khusus per-affiliator, fallback config global)
            'commissionRate' => $user->effectiveFee(),
            'commissionEarned' => \App\Models\Withdrawal::earned($user),
            'commissionBalance' => \App\Models\Withdrawal::balance($user),
            'withdrawals' => $user->has_withdrawals()->orderByDesc('id')->limit(5)->get(),
        ]);
    }

    /**
     * Simpan/ubah rekening bank reseller.
     */
    public function updateBank(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bank_name' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:100'],
            'bank_account_no' => ['required', 'string', 'max:30'],
        ]);

        Auth::user()->update($data);

        flash()->success('Rekening berhasil disimpan.');

        return redirect()->route('account.dashboard');
    }

    /**
     * Ajukan withdraw komisi. Saldo = earned - (pending + paid).
     */
    public function withdraw(Request $request): RedirectResponse
    {
        $this->authorizeAffiliator();

        $user = Auth::user();

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $balance = \App\Models\Withdrawal::balance($user);
        $amount = (float) $request->input('amount');
        $min = (float) config('commission.min_withdraw', 50000);

        if ($amount < $min) {
            return back()->withErrors(['amount' => 'Minimal pencairan Rp '.number_format($min, 0, ',', '.').'.']);
        }

        if ($amount > $balance) {
            return back()->withErrors(['amount' => 'Jumlah melebihi saldo yang bisa dicairkan (Rp '.number_format($balance, 0, ',', '.').').']);
        }

        if (empty($user->bank_account_no)) {
            return back()->withErrors(['amount' => 'Lengkapi data rekening bank terlebih dahulu.']);
        }

        \App\Models\Withdrawal::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'bank_name' => $user->bank_name,
            'bank_account_name' => $user->bank_account_name,
            'bank_account_no' => $user->bank_account_no,
            'status' => \App\Models\Withdrawal::STATUS_PENDING,
        ]);

        flash()->success('Pengajuan withdraw berhasil dikirim dan menunggu persetujuan admin.');

        return redirect()->route('account.dashboard');
    }

    // ------------------------------------------------------------------
    // Profile
    // ------------------------------------------------------------------

    public function profile(): View
    {
        return view('ecommerce::pages.account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        flash()->success('Profile berhasil diperbarui.');

        return redirect()->route('account.profile');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => bcrypt($request->input('password')),
        ]);

        flash()->success('Password berhasil diperbarui.');

        return redirect()->route('account.profile');
    }

    // ------------------------------------------------------------------
    // Customer (khusus affiliator)
    // ------------------------------------------------------------------

    public function customers(Request $request): View
    {
        $this->authorizeAffiliator();

        $q = trim((string) $request->input('q', ''));

        $data = User::query()
            ->where('type', UserTypeEnum::CUSTOMER)
            ->where('reference_id', Auth::id())
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('ecommerce::pages.account.customers', [
            'data' => $data,
            'q' => $q,
        ]);
    }

    public function customerCreate(): View
    {
        $this->authorizeAffiliator();

        return view('ecommerce::pages.account.customer-form', [
            'customer' => null,
        ]);
    }

    public function customerStore(Request $request): RedirectResponse
    {
        $this->authorizeAffiliator();

        $data = $this->validatedCustomer($request);

        User::create($data);

        flash()->success('Customer berhasil ditambahkan.');

        return redirect()->route('account.customers');
    }

    public function customerEdit(int $id): View
    {
        $this->authorizeAffiliator();

        $customer = $this->ownedCustomer($id);

        return view('ecommerce::pages.account.customer-form', [
            'customer' => $customer,
        ]);
    }

    public function customerUpdate(Request $request, int $id): RedirectResponse
    {
        $this->authorizeAffiliator();

        $customer = $this->ownedCustomer($id);
        $data = $this->validatedCustomer($request, $customer);

        $customer->update($data);

        flash()->success('Customer berhasil diperbarui.');

        return redirect()->route('account.customers');
    }

    public function customerDelete(int $id): RedirectResponse
    {
        $this->authorizeAffiliator();

        $customer = $this->ownedCustomer($id);
        $customer->delete();

        flash()->success('Customer berhasil dihapus.');

        return redirect()->route('account.customers');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Bar chart omzet 7 hari (Larapex/ApexCharts) dengan tinggi tetap.
     */
    private function omzetBarChart($dailySales)
    {
        $chart = new \ArielMejiaDev\LarapexCharts\LarapexChart;

        return $chart->barChart()
            ->addData($dailySales->pluck('total')->map(fn ($v) => round((float) $v))->toArray())
            ->setXAxis($dailySales->pluck('label')->toArray())
            ->setColors(['#388e3c'])
            ->setGrid()
            ->setHeight(300)
            // Placeholder __Y_FORMAT__ diganti fungsi JS di view karena
            // formatter harus berupa function, bukan JSON biasa.
            ->setOptions([
                'chart' => ['background' => '#ffffff', 'fontFamily' => 'inherit'],
                'grid' => ['borderColor' => '#e5e7eb', 'opacity' => 0.6],
                'yaxis' => [
                    'labels' => [
                        'formatter' => '__Y_FORMAT__',
                    ],
                ],
            ]);
    }

    private function authorizeAffiliator(): void
    {
        abort_unless(Auth::check() && Auth::user()->isAffiliator(), 403, 'Hanya affiliator yang dapat mengakses halaman ini.');
    }

    private function ownedCustomer(int $id): User
    {
        return User::where('type', UserTypeEnum::CUSTOMER)
            ->where('reference_id', Auth::id())
            ->findOrFail($id);
    }

    private function validatedCustomer(Request $request, ?User $existing = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($existing?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [$existing ? 'nullable' : 'required', 'string', 'min:6'],
        ]);

        $data['type'] = UserTypeEnum::CUSTOMER;
        $data['reference_id'] = Auth::id();

        // Password di-hash otomatis oleh cast 'hashed' di model User.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
