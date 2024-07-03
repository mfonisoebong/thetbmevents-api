<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function scopeFilter(Builder $builder)
    {
        $builder->when(request('search'), function ($builder) {
            $searchVal = '%' . request('search') . '%';
            $builder->where('first_name', 'like', $searchVal)
                ->orWhere('last_name', 'like', $searchVal)
                ->orWhere('email', 'like', $searchVal)
                ->orWhere('buisness_name', 'like', $searchVal);
        });

    }

    public function getFullNameAttribute()
    {
        $name = $this?->buisness_name ?? $this->first_name . ' ' . $this->last_name;
        return $name;
    }

    public function getAvatarAttribute($value)
    {
        $isGoogleAvatar = Str::isUrl($value);
        $avatar = $isGoogleAvatar ? $value : env('APP_URL') . '/' . $value;
        return !$value ? null : $avatar;
    }

    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    public function bankDetails()
    {
        return $this->hasOne(OrganizerBankDetails::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function commision()
    {
        return $this->hasOne(Commision::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }


    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function billingInfo()
    {
        return $this->hasOne(BillingInfo::class);
    }

    public function createdTickets()
    {
        return $this->hasMany(Ticket::class);
    }


    public function otpCodes()
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'organizer_id');
    }

    public function purchasedTickets()
    {
        return $this->hasMany(PurchasedTicket::class, 'user_id');
    }

    public function usersPurhcasedTickets()
    {
        return $this->hasMany(PurchasedTicket::class, 'organizer_id');
    }


}
