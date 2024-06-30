<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizerBankDetails extends Model
{
    use HasFactory, HasUuids;

    protected $fillable= [
        'user_id',
        'bank_name',
        'account_number',
        'account_name',
        'swift_code',
        'iban',
    ];
    public function organizer() {
        return $this->belongsTo(User::class);
    }
}
