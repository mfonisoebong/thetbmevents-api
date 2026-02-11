<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AdminOrganizersResource;
use App\Http\Resources\V2\OrganizerAttendeeResource;
use App\Mail\AccountReinstatedMail;
use App\Mail\AccountSuspendedMail;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\GetTopOrganizers;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    use GetTopOrganizers;

    public function listOrganizers()
    {
        return $this->success(AdminOrganizersResource::collection(User::where('role', 'organizer')->orderByDesc('created_at')->get()));
    }

    public function changeOrganizerStatus(string $organizer)
    {
        $organizer = User::findOrFail($organizer);

        $status = request()->input('status');

        $organizer->account_state = $status;
        $organizer->save();

        if ($status === 'suspended') {
            Mail::to($organizer->email)->send(new AccountSuspendedMail($organizer));
        } else {
            Mail::to($organizer->email)->send(new AccountReinstatedMail($organizer));
        }

        return $this->success('Organizer account status changed successfully.');
    }

    public function impersonateUser(User $user)
    {
        return $this->success([
            'token' => JWTAuth::fromUser($user),
            'user' => $user
        ]);
    }

    public function listAttendees()
    {
        return $this->success(OrganizerAttendeeResource::collection(Attendee::distinct()->take(10)->orderByDesc('created_at')->get()));
    }

    public function overview()
    {
        $revenueThisMonth = Transaction::where('status', 'success')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $totalEvents = Event::count();

        $eventsThisMonth = Event::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalOrganizers = User::where('role', 'organizer')->count();

        $revenuePast12Months = collect(range(0, 11))
            ->map(function (int $i) {
                $date = now()->copy()->startOfMonth()->subMonths($i);

                $revenue = Transaction::where('status', 'success')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount');

                return [
                    'month' => $date->format('F'),
                    'year' => (int) $date->format('Y'),
                    'revenue' => (float) $revenue,
                ];
            })
            // ->reverse()
            ->values();

        return $this->success([
            'revenue_this_month' => $revenueThisMonth,
            'total_events' => $totalEvents,
            'events_this_month' => $eventsThisMonth,
            'total_organizers' => $totalOrganizers,
            'revenue_past_12_months' => $revenuePast12Months,
            'top_organizers' => $this->computeTopOrganizers(),
        ]);
    }
}
