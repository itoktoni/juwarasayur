<?php

namespace Modules\So\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function __construct(User $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'resellerOptions' => $this->resellerOptions(),
        ], $data);
    }

    protected function getData()
    {
        $query = User::query()
            ->where('type', UserTypeEnum::CUSTOMER)
            ->with('hasReseller')
            ->filter()
            ->sort();

        // Reseller hanya melihat customer miliknya
        $user = Auth::user();
        if ($user && ! $user->isAdmin() && ! $user->isDeveloper()) {
            $query->where('reference_id', $user->id);
        }

        return $query;
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

            return $this->response($this->payload(TOAST_SUCCESS, $user));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $user = User::where('type', UserTypeEnum::CUSTOMER)->findOrFail($id);

        try {
            $data = $this->validated($request, $user);
            unset($data['avatar']);

            $existing = $user->avatar ?? null;
            $avatar = $this->handleAvatar($request, $existing);
            if ($avatar !== $existing) {
                $data['avatar'] = $avatar;
            }

            $user->update($data);

            return $this->response($this->payload(TOAST_SUCCESS, $user));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    // ---- avatar helpers (same pattern as UsersController) ----

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
     * Validasi + paksa type=customer & reference_id = reseller terkait.
     */
    private function validated(GeneralRequest $request, ?User $existing = null): array
    {
        $rules = (new User)->rules();

        $data = $request->validate(array_merge($rules, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => [$existing ? 'nullable' : 'required', 'string', 'min:6'],
            'reference_id' => ['nullable', 'integer', 'exists:users,id'],
            'avatar' => ['nullable', 'string', 'max:255'],
        ]));

        $auth = Auth::user();
        $data['type'] = UserTypeEnum::CUSTOMER;

        // Reseller tidak bisa menunjuk reference_id lain; admin boleh pilih reseller
        if ($auth && ! $auth->isAdmin() && ! $auth->isDeveloper()) {
            $data['reference_id'] = $auth->id;
        }
        $data['reference_id'] ??= $auth?->id;

        // Password di-hash otomatis oleh cast 'hashed' di model User.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }

    private function resellerOptions(): array
    {
        $auth = Auth::user();
        if ($auth && ($auth->isAdmin() || $auth->isDeveloper())) {
            return User::where('type', UserTypeEnum::RESELLER)->orderBy('name')->pluck('name', 'id')->all();
        }

        return [];
    }
}
