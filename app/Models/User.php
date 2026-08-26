<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Abbasudo\Purity\Traits\Filterable;
use Abbasudo\Purity\Traits\Sortable;
use App\Concerns\DefaultEntity;
use App\Concerns\OptionTrait;
use App\Enums\UserTypeEnum;
use App\Notifications\ResetPasswordNotification;
use App\Properties\UserEntity;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin IdeHelperUser
 */
#[Fillable(['name', 'email', 'password', 'role', 'type', 'reference_id', 'phone', 'avatar', 'verified_at', 'bank_name', 'bank_account_name', 'bank_account_no', 'fee'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use DefaultEntity, Filterable, HasApiTokens, HasFactory, Notifiable, OptionTrait, Sortable, TwoFactorAuthenticatable, UserEntity;

    protected $table = 'users';

    protected $keyType = 'int';

    protected $primaryKey = 'id';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
            'fee' => 'decimal:2',
        ];
    }

    /**
     * Columns available for filtering.
     */
    public static $filterColumns = [
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone',
        'role' => 'Role',
        'type' => 'Type',
    ];

    public static $sortColumns = [
        'name',
        'email',
        'phone',
        'role',
        'type',
    ];

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'email' => 'required|string',
            'role' => 'string',
            'type' => 'nullable|string|in:'.implode(',', UserTypeEnum::getValues()),
            'reference_id' => 'nullable|integer|exists:users,id',
            'password' => 'string',
            'avatar' => 'nullable|string|max:255',
        ];
    }

    public static function field_name(): string
    {
        return 'name';
    }

    /**
     * Get the user's avatar public URL. Empty string if none.
     */
    public function getAvatarUrlAttribute(): string
    {
        return fileUrl($this->avatar);
    }

    public function isDeveloper(): bool
    {
        return $this->role === 'developer';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    public function isReseller(): bool
    {
        return $this->type === UserTypeEnum::RESELLER;
    }

    public function isCustomer(): bool
    {
        return $this->type === UserTypeEnum::CUSTOMER;
    }

    /**
     * Fee komisi efektif (%): fee khusus reseller, fallback ke config global.
     * Hanya admin yang boleh mengubah fee ini (via menu Reseller).
     */
    public function effectiveFee(): float
    {
        return (float) ($this->fee ?? config('commission.rate', 2));
    }

    /**
     * Customers owned by this reseller (users with reference_id = reseller id).
     */
    public function hasCustomers(): HasMany
    {
        return $this->hasMany(User::class, 'reference_id');
    }

    /**
     * Pengajuan withdraw komisi milik reseller ini.
     */
    public function has_withdrawals(): HasMany
    {
        return $this->hasMany(\App\Models\Withdrawal::class);
    }

    /**
     * The reseller this customer belongs to.
     */
    public function hasReseller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reference_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
