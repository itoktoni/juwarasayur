<?php

namespace Modules\Reseller\Http\Controllers;

use App\Actions\DeleteAction;
use App\Http\Requests\GeneralRequest;
use Illuminate\Support\Facades\Auth;
use Modules\So\Http\Controllers\SoController as SoBaseController;
use Modules\So\Models\So;

class OrderController extends SoBaseController
{
    /**
     * Tampilan: table milik modul Reseller, form pakai milik modul So
     * (sudah lengkap: produk, customer, ongkir, dll).
     */
    protected function template($file = null, $folder = null, $core = false)
    {
        $action = 'table';

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
            if (isset($frame['function']) && preg_match('/^(get|post)/', $frame['function'])) {
                $action = strtolower(preg_replace('/^(get|post)/', '', $frame['function']));
                break;
            }
        }

        if (in_array($action, ['update', 'create'])) {
            $action = 'form';
        }

        if ($file) {
            $action = $file;
        }

        // Form order dipakai dari modul So agar tidak duplikasi UI
        if ($action === 'form') {
            return 'so::pages.so.form';
        }

        return 'reseller::pages.order.'.$action;
    }

    /**
     * Hanya order milik reseller yang login. Admin/developer melihat semua.
     */
    protected function getData()
    {
        $user = Auth::user();
        $query = $this->model->with(['has_reseller', 'has_customer', 'has_details.has_product']);

        if ($user && ! $user->isAdmin() && ! $user->isDeveloper()) {
            $query->where('so_id_reseller', $user->id);
        }

        return $query->filter()->sort();
    }

    /**
     * Pembuatan pesanan reseller dilakukan di halaman publik (storefront),
     * bukan di admin. Arahkan ke keranjang publik.
     */
    public function getCreate(GeneralRequest $request)
    {
        return redirect()->route('cart.index');
    }

    public function getUpdate(GeneralRequest $request, $id)
    {
        abort_unless($this->owns($id), 403, 'Pesanan bukan milik reseller ini.');

        $data = $this->model->with(['has_details.has_product'])->findOrFail($id);

        return $this->views($this->template(), [
            'model' => $data,
        ]);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        abort_unless($this->owns($id), 403, 'Pesanan bukan milik reseller ini.');

        return parent::postUpdate($request, $id);
    }

    public function getDelete(GeneralRequest $request, $id)
    {
        abort_unless($this->owns($id), 403, 'Pesanan bukan milik reseller ini.');

        $response = (new DeleteAction)->remove($id, $this->model);

        return $this->response($response);
    }

    public function postDelete(GeneralRequest $request)
    {
        $ids = (array) $request->input('ids', []);
        abort_unless($this->ownsMany($ids), 403, 'Ada pesanan yang bukan milik reseller ini.');

        $response = DeleteAction::run($request, $this->model);

        return $this->response($response);
    }

    /**
     * Cek kepemilikan pesanan terhadap reseller yang login.
     * Admin/developer bebas.
     */
    protected function owns($id): bool
    {
        $user = Auth::user();

        if (! $user || $user->isAdmin() || $user->isDeveloper()) {
            return true;
        }

        return So::where('id', $id)->where('so_id_reseller', $user->id)->exists();
    }

    protected function ownsMany(array $ids): bool
    {
        $user = Auth::user();

        if (! $user || $user->isAdmin() || $user->isDeveloper()) {
            return true;
        }

        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            return false;
        }

        $count = So::whereIn('id', $ids)->where('so_id_reseller', $user->id)->count();

        return $count === count($ids);
    }
}
