<?php

namespace App\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\Withdrawal;
use Illuminate\Validation\ValidationException;

/**
 * Admin: kelola pengajuan pencairan komisi reseller.
 * Proses = ubah status menjadi paid / rejected via form update.
 */
class WithdrawalController extends Controller
{
    use ControllerTrait;

    public function __construct(Withdrawal $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'statusOptions' => [
                Withdrawal::STATUS_PENDING => 'Pending',
                Withdrawal::STATUS_PAID => 'Dibayar',
                Withdrawal::STATUS_REJECTED => 'Ditolak',
            ],
        ], $data);
    }

    protected function getData()
    {
        // Pending di atas, lalu yang terbaru
        return $this->model->with(['has_user'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('id');
    }

    public function getUpdate(GeneralRequest $request, $id)
    {
        $data = $this->model->with(['has_user'])->findOrFail($id);

        return $this->views($this->template(), [
            'model' => $data,
        ]);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $withdrawal = $this->model->findOrFail($id);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', [Withdrawal::STATUS_PENDING, Withdrawal::STATUS_PAID, Withdrawal::STATUS_REJECTED])],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($withdrawal->status === Withdrawal::STATUS_PAID && ($data['status'] ?? '') !== Withdrawal::STATUS_PAID) {
            throw ValidationException::withMessages(['status' => 'Withdrawal yang sudah dibayar tidak bisa diubah.']);
        }

        $data['processed_at'] = now();
        $withdrawal->update($data);

        return $this->response($this->payload(TOAST_SUCCESS, $withdrawal));
    }
}
