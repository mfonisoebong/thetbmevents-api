<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\StoreTestimoniesRequest;
use App\Http\Requests\UpdateTestimoniesRequest;
use App\Models\Feature;
use App\Models\PageHeader;
use App\Models\Testimony;
use App\Traits\CheckUnMatched;
use App\Traits\HttpResponses;
use App\Traits\StoreImage;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class TestimoniesController extends Controller
{
    use HttpResponses, CheckUnMatched, StoreImage;
    public function store(StoreTestimoniesRequest $request){

        $avatars= $request->file('avatars');
        $names= $request->names;
        $descriptions= $request->descriptions;
        $channels= $request->channels;
        $isUnmatched= $this->isUnMatched($avatars, $names);
        $pageHeader= PageHeader::where('for', 'testimonies')
            ->first();

        if($isUnmatched){
            return $this->failed(400, null, 'Unmatched inputs');
        }

        if(!$pageHeader){
            PageHeader::create([
                'heading'=> $request->heading,
                'sub_heading'=> $request->sub_heading,
                'for'=> 'testimonies'
            ]);
        }

        foreach ($names as $index=> $name){
            try{
                $avatar= $avatars[$index];
                $description= $descriptions[$index];
                $channel= $channels[$index];
                $avatarFilepath= 'storage/testimonies/avatars/'.Str::uuid()->toString().'.webp';

                $this->storeImage($avatarFilepath, null, $avatar);
                Testimony::create([
                   'name'=> $name,
                   'description'=> $description,
                   'avatar'=> $avatarFilepath,
                    'channel'=> $channel,
                ]);

            } catch (\Exception){
                return $this->failed(500, null, 'An error occurred');

            }
        }
        return $this->success(null, 'Testimonies created');

    }

    public function getTestimonies(){
        $heading= PageHeader::where('for', 'testimonies')
            ->select(['heading', 'sub_heading'])
            ->first();
        $testimonies= Testimony::all(['name', 'description', 'avatar', 'channel', 'id']);

        return $this->success([
            'heading'=> $heading,
            'testimonies'=> $testimonies
        ]);
    }

    public function update(UpdateTestimoniesRequest $request){

        $names= $request->names;
        $ids= $request->ids;
        $descriptions= $request->descriptions;
        $channels= $request->channels;
        $isUnmatched= $this->isUnMatched($ids, $names);

        if($isUnmatched){
            return $this->failed(400, null, 'Unmatched inputs');
        }

        $pageHeader= PageHeader::where('for', 'testimonies')
            ->first();
        $pageHeader->update([
            'heading'=> $request->heading,
            'sub_heading'=> $request->sub_heading,
        ]);

        foreach ($ids as $index=> $id){
                $testimony= Testimony::where('id', $id)
                    ->first();
                $avatar= $request->file('avatar_'.$id)?? false;
                $name= $names[$index];

                $description= $descriptions[$index];
                $channel= $channels[$index];

                if($avatar){

                    $avatarFilepath= 'storage/testimonies/avatars/'.Str::uuid()->toString().'.webp';

                    $this->storeImage($avatarFilepath, $testimony->avatar, $avatar);

                    $testimony->update([
                        'name'=> $name,
                        'description'=> $description,
                        'channel'=> $channel,
                        'avatar'=> $avatarFilepath
                    ]);
                } else{
                    $testimony->update([
                        'name'=> $name,
                        'description'=> $description,
                        'channel'=> $channel,
                    ]);
                }




        }


        return $this->success(null, 'Testimonies updates');

    }

}
