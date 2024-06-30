<?php

namespace App\Http\Controllers\users;

use App\Http\Resources\FeatureResource;
use App\Http\Resources\SliderResource;
use App\Models\Feature;
use App\Models\PageHeader;
use App\Models\Slider;
use App\Models\Testimony;
use App\Traits\HttpResponses;
class HomePageController extends Controller
{
    use HttpResponses;
    public function getHomePageData(){
        $upcomingEvents= Slider::all();
        $slides= SliderResource::collection($upcomingEvents);
        $featuresHeading= PageHeader::where('for', 'features')
            ->select(['heading', 'sub_heading'])
            ->first();
        $testimoniesHeading= PageHeader::where('for', 'testimonies')
            ->select(['heading', 'sub_heading'])
            ->first();
        $features= FeatureResource::collection(Feature::all());
        $testimonies= Testimony::all(['name', 'channel', 'avatar', 'description', 'id']);

        return $this->success([
            'upcoming_events'=> $slides,
            'features'=> [
                'heading'=> $featuresHeading?->heading,
                'sub_heading'=> $featuresHeading?->sub_heading,
                'items'=> $features
            ],
            'testimonies'=> [
                'heading'=> $testimoniesHeading?->heading,
                'sub_heading'=> $testimoniesHeading?->sub_heading,
                'items'=> $testimonies
            ]
        ]);
    }
}
