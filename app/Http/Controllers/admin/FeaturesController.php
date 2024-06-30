<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\StoreFeaturesRequest;
use App\Http\Requests\UpdateFeaturesRequest;
use App\Http\Resources\FeatureResource;
use App\Models\Feature;
use App\Models\PageHeader;
use App\Traits\HttpResponses;
use App\Traits\CheckUnMatched;
use App\Traits\StoreImage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FeaturesController extends Controller
{
    use HttpResponses, CheckUnMatched, StoreImage;
    public function store(StoreFeaturesRequest $request){

        $features= $request->features;
        $thumbnails= $request->file('thumbnails');
        $pageHeader= PageHeader::where('for', 'features')
            ->first();

        $isUnMatched= $this->isUnMatched($features, $thumbnails);

        if($isUnMatched){
            return $this->failed(400, null, 'Features and thumbnails do not match');
        }

        if(!$pageHeader){
            PageHeader::create([
                'heading'=> $request->heading,
                'sub_heading'=> $request->sub_heading,
                'for'=> 'features'
            ]);
        }


        foreach ($features as $index=> $feature){
            try{
                $thumbnail= $thumbnails[$index];
                $thumbnailFilepath= 'storage/features/'.Str::uuid()->toString().'.webp';

                $this->storeImage($thumbnailFilepath,null,$thumbnail);

                Feature::create([
                    'title'=> $feature,
                    'thumbnail'=> str_replace('public', 'storage', $thumbnailFilepath),
                ]);
            } catch (\Exception){
                return $this->failed(500, null, 'An error occurred');
            }

        }

        return $this->success(null, 'Features created');
    }

    public function getFeatures(){
        $heading= PageHeader::where('for', 'features')
            ->select(['heading', 'sub_heading'])
            ->first();

        $features= Feature::all();

        return $this->success([
            'heading'=> $heading,
            'features'=> FeatureResource::collection($features)
        ]);
    }

    public function update(UpdateFeaturesRequest $request){

        $features= $request->features;
        $thumbnails= $request->file('thumbnails');
        $ids= $request->ids;
        $isUnMatched= $this->isUnMatched($features, $ids);


        if($isUnMatched){
            return $this->failed(400, null, 'Features and thumbnails do not match');
        }
        $pageHeader= PageHeader::where('for', 'features')
            ->first();
        $pageHeader->update([
            'heading'=> $request->heading,
            'sub_heading'=> $request->sub_heading,
        ]);
        foreach ($ids as $index=> $id){
            $feature= Feature::where('id', $id)
                ->first();
            $thumbnail= $request->file('thumbnail_'.$id)?? false;
            $title= $features[$index];

            if($thumbnail){
                    $thumbnailFilepath= 'storage/features/'.Str::uuid()->toString().'.webp';
                    $this->storeImage($thumbnailFilepath,$feature->thumbnail,$thumbnail);
                $feature->update([
                    'title'=> $title,
                    'thumbnail'=> $thumbnailFilepath
                ]);
            } else{
                $feature->update([
                    'title'=> $title,
                ]);
            }


        }
        return $this->success(null, 'Features updated');

    }



}
