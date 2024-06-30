<?php

namespace App\Http\Controllers\admin;

use App\Http\Requests\StoreGeneralSettings;
use App\Http\Requests\UpdateGeneralSettings;
use App\Models\GeneralSettings;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
class GeneralSettingsController extends Controller
{
    use HttpResponses;
    public function store(StoreGeneralSettings $request){
        $generalSettings= GeneralSettings::first();
        $logoLightImage= $request->file('site_logo_light');
        $logoDarkImage= $request->file('site_logo_dark');
        $siteLogoLightFileName= Str::uuid().'.'.$logoLightImage->getClientOriginalExtension();
        $siteLogoDarkFileName= Str::uuid().'.'.$logoDarkImage->getClientOriginalExtension();

        $logoLightImage->move('storage/general', $siteLogoLightFileName);
        $logoDarkImage->move('storage/general', $siteLogoDarkFileName);



        if(!$generalSettings){
            GeneralSettings::create([
                'site_name'=>  $request->site_name,
                'site_description'=>  $request->site_description,
                'site_logo_light'=>  'storage/general/'.$siteLogoLightFileName,
                'site_logo_dark'=>  'storage/general/'.$siteLogoDarkFileName
            ]);
        }
        return $this->success(null, 'General settings updated');
    }

    public function update(UpdateGeneralSettings $request){
        $generalSettings= GeneralSettings::first();
        $hasSiteLogoLight= $request->hasFile('site_logo_light');
        $hasSiteLogoDark= $request->hasFile('site_logo_dark');
        $logoLightImage= $request->file('site_logo_light');
        $logoDarkImage= $request->file('site_logo_dark');

        if(!$generalSettings){
            return $this->failed(400);
        }

        if($hasSiteLogoLight){
            $siteLogoLightName= Str::uuid().'.'.$logoLightImage->getClientOriginalExtension();

            $oldLogoLightPath= public_path($generalSettings->site_logo_light);
            File::delete($oldLogoLightPath);
            $logoLightImage->move('storage/general', $siteLogoLightName);
            $generalSettings->site_logo_light= 'storage/general/'.$siteLogoLightName;
        }


        if($hasSiteLogoDark){
            $siteLogoDarkName= Str::uuid().'.'.$logoDarkImage->getClientOriginalExtension();

            $oldLogoLightPath= public_path($generalSettings->site_logo_dark);
            File::delete($oldLogoLightPath);
            $logoDarkImage->move('storage/general', $siteLogoDarkName);
            $generalSettings->site_logo_dark= 'storage/general/'.$siteLogoDarkName;
        }

        $generalSettings->site_name= $request->site_name;
        $generalSettings->site_description= $request->site_description;

        $generalSettings->save();

        return $this->success(null, 'General settings updated');

    }
}
