<?php

namespace Modules\So\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\User;
use App\Models\Withdrawal;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Modules\So\Enums\SoStatusEnum;
use Modules\So\Models\So;
use Modules\So\Models\SoDetail;

/**
 * Admin: kelola user bertipe affiliator (mirror ResellerController).
 * Affiliator bisa memiliki customers (reference_id) sama seperti reseller.
 */
class AffiliatorController extends Controller
{
    public function __construct(User $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        // Model yang sedang diedit datang lewat $data['model'] — $this->model
        // tetap instance kosong (ControllerTrait::getUpdate tidak mengisinya).
        $record = $data['model'] ?? null;
        $extras = ['customerOptions' => $this->customerOptions()];

        if ($record instanceof User && $record->exists) {
            $extras['selectedCustomerIds'] = User::where('type', UserTypeEnum::CUSTOMER)
                ->where('reference_id', $record->id)
                ->pluck('id')
                ->all();

            $summary = $this->commissionSummary($record, $this->includePendingOrders());
            $extras['commission'] = $summary;
            $extras['commissionChart'] = $this->commissionChart($summary['trend']);
        } else {
            $extras['selectedCustomerIds'] = old('customer_ids', []);
        }

        return array_merge([
            'model' => $this->model,
        ], $extras, $data);
    }

    /**
     * Status yang dihitung untuk komisi/omzet.
     * Default ($includePending = true): semua status KECUALI Cancelled, jadi
     * order pending ikut terhitung. Mode "hanya terbayar" membuang pending.
     */
    private function countedStatuses(bool $includePending): array
    {
        $paid = [
            SoStatusEnum::PAID,
            SoStatusEnum::CONFIRMED,
            SoStatusEnum::SHIPPED,
            SoStatusEnum::DELIVERED,
        ];

        return $includePending ? array_merge([SoStatusEnum::PENDING], $paid) : $paid;
    }

    /**
     * Default dashboard: order pending ikut dihitung, hanya Cancelled yang
     * dikecualikan. Tombol di dashboard mengirim include_pending=0 untuk
     * membalik ke mode "hanya order terbayar".
     */
    private function includePendingOrders(): bool
    {
        return request()->boolean('include_pending', true);
    }

    /**
     * Total komisi (snapshot fee_amount) untuk sekumpulan status order.
     */
    private function commissionFor(User $affiliator, array $statuses): float
    {
        return (float) SoDetail::query()
            ->whereHas('has_so', fn ($q) => $q->where('so_id_reseller', $affiliator->id)->whereIn('so_status', $statuses))
            ->sum('fee_amount');
    }

    /**
     * Ringkasan komisi & performa affiliator untuk dashboard di halaman update.
     * Komisi dihitung dari snapshot so_order_details.fee_amount milik SO yang
     * dimiliki affiliator ini (so_id_reseller), sama seperti Withdrawal::earned().
     */
    private function commissionSummary(User $affiliator, bool $includePending = true): array
    {
        $statuses = $this->countedStatuses($includePending);

        $orders = So::where('so_id_reseller', $affiliator->id);

        $omzet = (float) (clone $orders)->whereIn('so_status', $statuses)->sum('so_grand_total');

        // Saat pending tidak dihitung, ambil dari Withdrawal::earned() agar
        // angkanya identik dengan sumber komisi yang dipakai untuk pencairan.
        $earned = $includePending
            ? $this->commissionFor($affiliator, $statuses)
            : Withdrawal::earned($affiliator);
        $withdrawn = Withdrawal::withdrawn($affiliator);

        return [
            'includePending' => $includePending,
            'withdrawn' => $withdrawn,
            'balance' => max(0, $earned - $withdrawn),
            'pending' => $this->commissionFor($affiliator, [SoStatusEnum::PENDING]),
            'rate' => $affiliator->effectiveFee(),
            'omzet' => $omzet,
            'orderCount' => (clone $orders)->whereIn('so_status', $statuses)->count(),
            'customerCount' => $affiliator->hasCustomers()->count(),
            'recentOrders' => (clone $orders)
                ->whereIn('so_status', $statuses)
                ->withCount('has_details')
                ->withSum('has_details as commission_total', 'fee_amount')
                ->orderByDesc('so_tanggal')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
            'withdrawals' => $affiliator->has_withdrawals()->orderByDesc('id')->limit(5)->get(),
            'trend' => $this->commissionTrend($affiliator, $statuses),
        ];
    }

    /**
     * Komisi per bulan untuk 6 bulan terakhir (termasuk bulan berjalan).
     * Bulan tanpa order tetap dikirim dengan nilai 0 agar sumbu chart konsisten.
     */
    private function commissionTrend(User $affiliator, array $earnedStatuses): Collection
    {
        $since = now()->startOfMonth()->subMonths(5);

        $komisiPerBulan = SoDetail::query()
            ->join('so_orders', 'so_orders.id', '=', 'so_order_details.so_detail_id_so')
            ->where('so_orders.so_id_reseller', $affiliator->id)
            ->whereIn('so_orders.so_status', $earnedStatuses)
            ->where('so_orders.so_tanggal', '>=', $since)
            ->selectRaw("DATE_FORMAT(so_orders.so_tanggal, '%Y-%m') as ym, SUM(so_order_details.fee_amount) as total")
            ->groupBy('ym')
            ->get()
            ->pluck('total', 'ym');

        return collect(range(5, 0))->map(function (int $i) use ($komisiPerBulan) {
            $month = now()->startOfMonth()->subMonths($i);

            return [
                'label' => $month->format('M Y'),
                'komisi' => (float) ($komisiPerBulan[$month->format('Y-m')] ?? 0),
            ];
        })->values();
    }

    private function commissionChart(Collection $trend): LarapexChart
    {
        return (new LarapexChart)->barChart()
            ->addData($trend->pluck('komisi')->map(fn ($v) => (int) round($v))->all(), 'Komisi')
            ->setXAxis($trend->pluck('label')->all())
            ->setColors(['#388e3c'])
            ->setDataLabels(false)
            ->setGrid()
            ->setHeight(300);
    }

    private function customerOptions(): array
    {
        return User::where('type', UserTypeEnum::CUSTOMER)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'reference_id'])
            ->mapWithKeys(function ($u) {
                $label = $u->name;
                if ($u->phone) {
                    $label .= ' ('.$u->phone.')';
                }
                if ($u->reference_id) {
                    $owner = User::find($u->reference_id);
                    if ($owner) {
                        $label .= ' — milik: '.$owner->name.($owner->type === UserTypeEnum::AFFILIATOR ? ' (Affiliator)' : '');
                    }
                } else {
                    $label .= ' — tanpa pemilik';
                }

                return [$u->id => $label];
            })->all();
    }

    protected function getData()
    {
        return User::query()
            ->where('type', UserTypeEnum::AFFILIATOR)
            ->filter()
            ->sort();
    }

    public function postCreate(GeneralRequest $request)
    {
        try {
            $data = $this->validated($request);

            $avatar = $this->handleAvatar($request, null);
            if ($avatar !== null) {
                $data['avatar'] = $avatar;
            }

            $user = User::create($data);
            $this->syncCustomers($user->id, $request->input('customer_ids'));

            return $this->response($this->payload(TOAST_SUCCESS, $user));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $user = User::where('type', UserTypeEnum::AFFILIATOR)->findOrFail($id);

        try {
            $data = $this->validated($request, $user);
            unset($data['avatar']);

            $existing = $user->avatar ?? null;
            $avatar = $this->handleAvatar($request, $existing);
            if ($avatar !== $existing) {
                $data['avatar'] = $avatar;
            }

            $user->update($data);
            $this->syncCustomers($user->id, $request->input('customer_ids'));

            return $this->response($this->payload(TOAST_SUCCESS, $user));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    /**
     * Sync multiple customers ke affiliator ini.
     * Jika field customer_ids tidak ada tapi customer_ids_submitted ada => lepas semua ([]).
     */
    private function syncCustomers(int $affiliatorId, $ids): void
    {
        // Deteksi form affiliator: ada flag hidden
        if ($ids === null && request()->has('customer_ids_submitted')) {
            $ids = [];
        }

        if ($ids === null) {
            return;
        }

        $ids = is_array($ids) ? array_map('intval', $ids) : [];
        $ids = array_filter(array_unique($ids));

        // Lepas customers yang sebelumnya milik affiliator ini tapi tidak ada di list baru
        User::where('type', UserTypeEnum::CUSTOMER)
            ->where('reference_id', $affiliatorId)
            ->when(! empty($ids), fn ($q) => $q->whereNotIn('id', $ids))
            ->update(['reference_id' => null]);

        // Assign customers terpilih ke affiliator ini (reassign dari pemilik lain juga)
        if (! empty($ids)) {
            User::where('type', UserTypeEnum::CUSTOMER)
                ->whereIn('id', $ids)
                ->update(['reference_id' => $affiliatorId]);
        }
    }

    // ---- avatar helpers (same pattern as ResellerController) ----

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

        if ($request->boolean('remove_avatar')) {
            $this->deleteUserFile($existing);

            return null;
        }

        return $existing;
    }

    private function deleteUserFile(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $file = storage_path('app/public/'.$path);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Validasi + paksa type=affiliator.
     */
    private function validated(GeneralRequest $request, ?User $existing = null): array
    {
        $rules = (new User)->rules();

        $data = $request->validate(array_merge($rules, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'password' => [$existing ? 'nullable' : 'required', 'string', 'min:6'],
            'reference_id' => ['nullable', 'integer', 'exists:users,id'],
            'avatar' => ['nullable', 'string', 'max:255'],
            'fee' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'consignasi' => ['nullable', 'boolean'],
        ]));

        $data['fee'] = $data['fee'] ?? ($existing?->fee);
        $data['consignasi'] = $request->boolean('consignasi');

        $data['type'] = UserTypeEnum::AFFILIATOR;

        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
