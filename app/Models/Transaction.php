<?php

namespace App\Models;

use App\Mail\PurchasedTicketMail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'gateway',
        'cart_items',
        'reference',
        'status',
        'coupon_id',
        'coupon_amount',
        'amount',
        'charged_amount',
        'organizer_id',
        'user_id',
        'data',
    ];

    protected $casts = [
        'amount' => 'float',
        'charged_amount' => 'float',
        'cart_items' => 'array',
        'data' => 'array',
    ];

    public function scopeFilter(Builder $builder)
    {
        $builder->when(request('from'), function ($builder) {
            $startDate = Carbon::parse(request('from'));
            $endDate = Carbon::parse(request('to'));
            $builder->whereBetween('created_at', [$startDate, $endDate]);
        });

        $builder->when(request('year'), function ($builder) {
            $builder
                ->whereYear('created_at', request('year'))
                ->whereMonth('created_at', request('month'));
        });


        $builder->when(request('today'), function ($builder) {
            $today = Carbon::now()
                ->toDateString();
            $builder->whereDate('created_at', $today);
        });

        $builder->when(request('yesterday'), function ($builder) {
            $yesterday = Carbon::yesterday()
                ->toDateString();
            $builder->whereDate('created_at', $yesterday);
        });

        $builder->when(request('past_three_months'), function ($builder) {
            $threeMonthsAgo = Carbon::now()->subMonths(3);
            $builder->where('created_at', '>=', $threeMonthsAgo);
        });

        return $builder;
    }


    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->hasMany(PurchasedTicket::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function newPurchasedTickets(): HasMany
    {
        return $this->hasMany(NewPurchasedTicket::class);
    }

    public function sendInvoice()
    {
        $purchasedTickets = $this->tickets;
        $customer = $this->customer;

        foreach ($purchasedTickets as $ticket) {
            $datePurchased = Carbon::parse($ticket->invoice->created_at)
                ->format('d/m/y');
            $timePurchased = Carbon::parse($ticket->invoice->created_at)
                ->format('H:i:s');
            $qrCodeData = json_encode([
                'id' => $ticket->id,
                'event_id' => $ticket->ticket->event_id,
                'quantity' => $ticket->quantity,
                'price' => $ticket->price
            ]);
            $eventLink = $ticket->ticket->event?->event_link;
            $eventLocation = $ticket->ticket->event?->location;
            $eventLocationTips = $ticket->ticket->event?->location_tips;
            $qrCode = QrCode::format('png')
                ->size(150)
                ->generate($qrCodeData);
            $ticketPath = 'tickets/' . Str::uuid()->toString() . '.png';

            Storage::disk('public')
                ->put($ticketPath, $qrCode);
            $qrCodeUrl = config('app.url') . '/storage/' . $ticketPath;


            $data = [
                'id' => $ticket->id,
                'event_title' => $ticket->ticket->event?->title ?? '',
                'organizer' => $ticket->ticket->organizer->full_name,
                'event_logo' => $ticket->ticket->event?->logo ?? '',
                'name' => $ticket->ticket->name . ' - ' . $ticket->ticket->event?->title ?? '',
                'price' => $ticket->quantity * $ticket->ticket->price,
                'event_link' => $eventLink,
                'event_location' => $eventLocation,
                'event_location_tips' => $eventLocationTips,
                'quantity' => $ticket->quantity,
                'gateway' => Str::upper($ticket->invoice->gateway),
                'date_purchased' => $datePurchased,
                'time_purchased' => $timePurchased,
                'qr_code' => $qrCodeUrl
            ];

            Mail::to($ticket->attendee->email)
                ->send(new PurchasedTicketMail($data, $ticket->attendee));
        }
    }
}
