<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\UpdateSliderRequest;
use App\Http\Resources\SliderResource;
use App\Models\Event;
use App\Models\Slider;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SlidersController extends Controller
{
    use HttpResponses;

    public function getSliders(){
        $sliders= Slider::all();
        $slidersData= SliderResource::collection($sliders);
        return $this->success($slidersData);
    }

    public function update(UpdateSliderRequest $request){
        $request->validate($request->all());

        Slider::truncate();

        foreach($request->sliders as $slide){
            $eventId= $slide['event_id'];
            $event= Event::where('id', $eventId)->first();

            if(!$event) {
                return $this->failed(400, null, $eventId.' is not a valid Event id');
            }

            Slider::create([
                'event_id'=> $eventId
            ]);
        }

        return $this->success(null, 'Sliders updated successfully');
        
    }
}
