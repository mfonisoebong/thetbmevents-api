<?php

namespace App\Http\Controllers\users;

use App\Mail\TestMail;
use App\Models\Event;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

class DevController extends Controller
{
    public function migrateAndSeed(){
        Artisan::call('migrate:refresh --seed --force');
    }
    public function migrateRefresh(){
        Artisan::call('migrate:refresh --force');
    }

    public function migrate(){
        Artisan::call('migrate --force');
    }

    public function storageLink(){
        Artisan::call('storage:link');
    }

    public function  testFileUpload(Request $request){
        if($request->hasFile('test_file')){
            $file= $request->file('test_file')
                ->storeAs('images', 'filename-2.jpg', 'public');
            return response()->json(['message'=> $file]);
        }
    }

    public function sendTestEmail(Request $request){
        $email= $request->email;


        return response()->json([
            'email'=> $email,
            'message'=> 'Email sent'
        ]);
    }

    public function testWebhook(Request $request){

        error_log(json_encode($request->all()));

        return response(null, 200);
    }

}
