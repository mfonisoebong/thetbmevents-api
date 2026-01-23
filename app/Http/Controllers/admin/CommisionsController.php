<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\StoreCommisionRequest;
use App\Http\Requests\UpdateCommisionRequest;
use App\Models\Commision;
use App\Models\User;
use App\Traits\ApiResponses;
use App\Traits\HttpResponses;
use Illuminate\Routing\Controller;

class CommisionsController extends Controller
{
    use HttpResponses, ApiResponses;

    public function store(StoreCommisionRequest $request){
        $request->validated($request->all());

        $commision= Commision::create([
            'rate'=> $request->rate,
            'user_id'=> $request->organizer_id
        ]);

        return $this->success($commision, 'Commision created successfully');
    }

    public function update(Commision $commision, UpdateCommisionRequest $request){
        $request->validated($request->all());

        $commision->update([
            'rate'=> $request->rate
        ]);

        return $this->success($commision, 'Commision udpated successfully');
    }

    public function destroy(Commision $commision){
        $commision->delete();
        return $this->success(null, 'Commision deleted successfully');
    }
}
