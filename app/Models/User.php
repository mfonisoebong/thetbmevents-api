<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'role',
        'country',
        'email',
        'phone_number',
        'account_state',
        'phone_dial_code',
        'avatar',
        'completed_profile',
        'auth_provider',
        'buisness_name',
        'password',
        'email_verified_at',
        'admin_role',
        'super_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
        'password'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function scopeFilter(Builder $builder): void
    {
        $builder->when(request('search'), function ($builder) {
            $searchVal = '%' . request('search') . '%';
            $builder->where('first_name', 'like', $searchVal)
                ->orWhere('last_name', 'like', $searchVal)
                ->orWhere('email', 'like', $searchVal)
                ->orWhere('buisness_name', 'like', $searchVal);
        });

    }

    public function getFullNameAttribute(): string
    {
        return $this->buisness_name ?? $this->first_name . ' ' . $this->last_name;
    }

    public function getAvatarPathAttribute(): string
    {
        return str_replace(config('app.url') . '/', '', $this->avatar);
    }

    public function getAvatarAttribute($value): ?string
    {
        $isGoogleAvatar = Str::isUrl($value);
        $avatar = $isGoogleAvatar ? $value : config('app.url') . '/' . $value;
        return !$value ? null : $avatar;
    }


    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    public function bankDetails(): HasOne
    {
        return $this->hasOne(OrganizerBankDetails::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function commision(): HasOne
    {
        return $this->hasOne(Commision::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreferences::class);
    }


    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'organizer_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function billingInfo(): HasOne
    {
        return $this->hasOne(BillingInfo::class);
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'organizer_id');
    }


    public function otpVerifications(): HasMany
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'organizer_id');
    }

    public function purchasedTickets(): HasMany
    {
        return $this->hasMany(PurchasedTicket::class, 'user_id');
    }

    public function usersPurhcasedTickets(): HasMany
    {
        return $this->hasMany(PurchasedTicket::class, 'organizer_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class, 'user_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }


}
