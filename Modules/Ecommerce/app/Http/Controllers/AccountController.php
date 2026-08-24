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

/**
 * Controller publik untuk area akun (profile, customer reseller).
 * Diakses dari halaman depan tanpa middleware 'admin' — modul-modul
 * CRUD lain adalah area admin dan tidak boleh dibuka reseller/customer.
 */
class AccountController extends Controller
{
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
    // Customer (khusus reseller)
    // ------------------------------------------------------------------

    public function customers(Request $request): View
    {
        $this->authorizeReseller();

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
        $this->authorizeReseller();

        return view('ecommerce::pages.account.customer-form', [
            'customer' => null,
        ]);
    }

    public function customerStore(Request $request): RedirectResponse
    {
        $this->authorizeReseller();

        $data = $this->validatedCustomer($request);

        User::create($data);

        flash()->success('Customer berhasil ditambahkan.');

        return redirect()->route('account.customers');
    }

    public function customerEdit(int $id): View
    {
        $this->authorizeReseller();

        $customer = $this->ownedCustomer($id);

        return view('ecommerce::pages.account.customer-form', [
            'customer' => $customer,
        ]);
    }

    public function customerUpdate(Request $request, int $id): RedirectResponse
    {
        $this->authorizeReseller();

        $customer = $this->ownedCustomer($id);
        $data = $this->validatedCustomer($request, $customer);

        $customer->update($data);

        flash()->success('Customer berhasil diperbarui.');

        return redirect()->route('account.customers');
    }

    public function customerDelete(int $id): RedirectResponse
    {
        $this->authorizeReseller();

        $customer = $this->ownedCustomer($id);
        $customer->delete();

        flash()->success('Customer berhasil dihapus.');

        return redirect()->route('account.customers');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function authorizeReseller(): void
    {
        abort_unless(Auth::check() && Auth::user()->isReseller(), 403, 'Hanya reseller yang dapat mengakses halaman ini.');
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
