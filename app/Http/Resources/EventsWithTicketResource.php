<?php

namespace App\Http\Resources;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class EventsWithTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $sales = Sale::where('event_id', $this->id)
            ->select('tickets_bought')
            ->get()
            ->toArray();
        $ticketsSoldArr = array_map(function ($sale) {
            return $sale['tickets_bought'];
        }, $sales);
        $ticketsSold = array_reduce($ticketsSoldArr, function ($a, $b) {
            return $a + $b;
        }) ?? 0;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'title' => Str::of($this->title)->limit(17) ?? $this->title,
            'type' => $this->type,
            'logo' => $this->image_url,
            'tickets_sold' => $ticketsSold
        ];
    }
}
