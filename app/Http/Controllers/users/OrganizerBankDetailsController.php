<?php

namespace App\Http\Controllers\users;


use App\Http\Requests\StoreOrganizerBankDetailsRequest;
use App\Http\Requests\UpdateOrganizerBankDetailsRequest;
use App\Http\Resources\OrganizerBankDetailsResource;
use App\Models\OrganizerBankDetails;
use App\Traits\ApiResponses;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OrganizerBankDetailsController extends Controller
{
    use HttpResponses, ApiResponses;

    public function getBankDetails(Request $request)
    {
        $user = $request->user();

        $bankDetails = $user?->bankDetails ? [
            'id' => $user->bankDetails->id,
            'bank_name' => $user->bankDetails->bank_name,
            'account_number' => $user->bankDetails->account_number,
            'account_name' => $user->bankDetails->account_name,
            'swift_code' => $user->bankDetails->swift_code,
            'iban' => $user->bankDetails->iban,
        ] : null;
        return $this->success($bankDetails);
    }

    public function store(StoreOrganizerBankDetailsRequest $request)
    {
        $request->validated($request->all());
        $user = $request->user();

        $bankDetails = OrganizerBankDetails::updateOrCreate([
            'user_id' => $user->id
        ], [
            'user_id' => $user->id,
            'bank_name' => $request->bank_name,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'swift_code' => $request->swift_code,
            'iban' => $request->iban
        ]);
        return $this->success(new OrganizerBankDetailsResource($bankDetails), 'Bank details created successfully');
    }

    public function update(UpdateOrganizerBankDetailsRequest $request)
    {


        $request->validated($request->all());
        $bankDetails = $request->user()?->bankDetails;

        if (!$bankDetails) {
            return $this->failed(400, 'No bank details');

        }

        $bankDetails->update($request->all());
        return $this->success($bankDetails, 'Bank details updated successfully');

    }

    public function destroy(Request $request)
    {

        $bankDetails = $request->user()?->bankDetails;

        if (!$bankDetails) {
            return $this->failed(400, 'No bank details');
        }
        $bankDetails->delete();
        return $this->success(null, 'Bank details deleted successfully');
    }
}
