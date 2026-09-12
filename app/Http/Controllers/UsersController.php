<?php

namespace App\Http\Controllers;

use App\Actions\CreateAction;
use App\Actions\UpdateAction;
use App\Concerns\ControllerTrait;
use App\Enums\RoleEnum;
use App\Http\Requests\GeneralRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{
    use ControllerTrait;

    protected function share($data = [])
    {
        $default = [
            'model' => $this->model,
            'role' => RoleEnum::getOptions(),
        ];

        return array_merge($default, $data);
    }

    public function __construct(User $model)
    {
        $this->model = $model::getModel();
    }

    public static function boot()
    {
        parent::saving(function ($model) {
            if (! empty(request()->get('password'))) {
                $model->password = Hash::make(request()->get('password'));
            }
        });
        parent::boot();
    }

    // ---- avatar helpers (same pattern as WebsiteSettingController) ----

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

    public function postCreate(GeneralRequest $request)
    {
        $response = CreateAction::run($request, $this->model);
        $avatar = $this->handleAvatar($request, null);
        if ($avatar !== null) {
            $request->merge(['avatar' => $avatar]);
        }

        return $this->response($response);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $response = UpdateAction::run($request, $id, $this->model);

        $user = $this->model->findOrFail($id);
        $existing = $user->avatar ?? null;

        $avatar = $this->handleAvatar($request, $existing);
        if ($avatar !== $existing) {
            $request->merge(['avatar' => $avatar]);
        }

        return $this->response($response);
    }

    /**
     * Approve affiliator/reseller: set verified_at = now() (admin approval).
     * Hanya untuk tipe affiliator/reseller yang masih pending.
     */
    public function postApprove(GeneralRequest $request, $id)
    {
        $user = $this->model->findOrFail($id);

        if (! in_array($user->type, [\App\Enums\UserTypeEnum::AFFILIATOR, \App\Enums\UserTypeEnum::RESELLER], true)) {
            return $this->response(['code' => 422, 'status' => false, 'message' => 'Hanya affiliator/reseller yang perlu approval', 'data' => null]);
        }

        if (! empty($user->verified_at)) {
            return $this->response(['code' => 200, 'status' => true, 'message' => 'Sudah di-approve sebelumnya', 'data' => $user]);
        }

        $user->update(['verified_at' => now()]);

        return $this->response(['code' => 200, 'status' => true, 'message' => 'Affiliate di-approve', 'data' => $user]);
    }

    public function postReject(GeneralRequest $request, $id)
    {
        $user = $this->model->findOrFail($id);

        if (! in_array($user->type, [\App\Enums\UserTypeEnum::AFFILIATOR, \App\Enums\UserTypeEnum::RESELLER], true)) {
            return $this->response(['code' => 422, 'status' => false, 'message' => 'Hanya affiliator/reseller', 'data' => null]);
        }

        // Reject = hapus verified_at + optional: set type ke user biasa atau blokir
        $user->update(['verified_at' => null]);

        return $this->response(['code' => 200, 'status' => true, 'message' => 'Approval dibatalkan', 'data' => $user]);
    }
}
