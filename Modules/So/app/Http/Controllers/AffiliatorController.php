<?php

namespace Modules\So\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

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
        $extras = [];
        if ($this->model->exists) {
            $extras['customerOptions'] = $this->customerOptions();
            $extras['selectedCustomerIds'] = User::where('type', UserTypeEnum::CUSTOMER)
                ->where('reference_id', $this->model->id)
                ->pluck('id')
                ->all();
        } else {
            $extras['customerOptions'] = $this->customerOptions();
            $extras['selectedCustomerIds'] = old('customer_ids', []);
        }

        return array_merge([
            'model' => $this->model,
        ], $extras, $data);
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
