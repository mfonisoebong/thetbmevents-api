<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name',
        'business_name',
        'email',
        'password',
        'country',
        'phone_number',
        'role',
        'avatar',
        'auth_provider',
        'email_verified_at',
        'account_state',
        'completed_profile',
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
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function scopeFilter(Builder $builder): void
    {
        $builder->when(request('search'), function ($builder) {
            $searchVal = '%' . request('search') . '%';
            $builder->where('full_name', 'like', $searchVal)
                ->orWhere('email', 'like', $searchVal)
                ->orWhere('business_name', 'like', $searchVal);
        });

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
        return $this->hasMany(Transaction::class, 'organizer_id');
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
