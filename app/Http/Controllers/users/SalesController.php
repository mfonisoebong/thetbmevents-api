<?php

namespace App\Http\Controllers\users;

use App\Http\Resources\SalesResource;
use App\Models\Sale;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    use HttpResponses;

    public function getSales(Request $request)
    {


        $user = $request->user();

        $sales = Sale::where('organizer_id', $user->id)
            ->paginate(20);

        $salesMetaData = $sales->toArray();

        $formattedSalesResource = SalesResource::collection($sales);

        $formattedSales = [...$salesMetaData, 'data' => $formattedSalesResource];
        return $this->success($formattedSales);
    }

    public function resendPurchasedTickets(Sale $sale)
    {

        $invoice = $sale->invoice;

        $invoice->sendInvoice();

        return $this->success(null, 'Invoice has been resent');
    }
}
