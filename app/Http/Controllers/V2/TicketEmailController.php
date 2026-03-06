<?php

namespace App\Http\Controllers\V2;

use App\Actions\ResendPurchasedTicketsFromReference;
use App\Enums\StatusCode;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Throwable;

class TicketEmailController extends Controller
{
    /**
     * Public endpoint to resend purchased ticket email(s) by transaction reference.
     */
    public function resendPurchasedTicketsFromReference(string $reference, ResendPurchasedTicketsFromReference $resender): JsonResponse
    {
        try {
            $resender->handle($reference);

            return $this->success(
                ['reference' => $reference],
                'Ticket email resent successfully',
                StatusCode::Success->value
            );
        } catch (ModelNotFoundException $e) {
            return $this->error('Transaction not found for reference: ' . $reference, StatusCode::NotFound->value);
        } catch (Throwable $e) {
            return $this->error('Failed to resend ticket email', StatusCode::InternalServerError->value, [
                'reference' => $reference,
            ]);
        }
    }
}

