<?php

namespace App\Http\Controllers\admin;

use App\Events\PasswordTokenCreated;
use App\Events\UserRegistered;
use App\Http\Requests\ExportUsersRequest;
use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateOrganizerSettingsRequest;
use App\Http\Resources\OrganizerResource;
use App\Http\Resources\UserResource;
use App\Mail\ActivatedOrganizer;
use App\Mail\ActivatedOrganizerMail;
use App\Models\Commision;
use App\Models\OrganizerBankDetails;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use League\Csv\Writer;

class UsersController extends Controller
{
    use HttpResponses;
    public function getUsers(){

        $users= User::filter()
        ->where('role', '=', 'individual')
        ->paginate(20);
        $paginateMetaData= $users->toArray();
        $usersResource= UserResource::collection($users);
        $data= [...$paginateMetaData, 'data'=> $usersResource];
        return $this->success($data);
    }

    public function getUser(User $user){
        $userData= new UserResource($user);
        return $this->success($userData);
    }

    public function getOrganizers(){
        $users= User::filter()
            ->where('role', '=', 'organizer')
            ->paginate(20);


        $paginateMetaData= $users->toArray();
        $usersResource= UserResource::collection($users);
        $data= [...$paginateMetaData, 'data'=> $usersResource];
        return $this->success($data);
    }

    public function getAdmins(){
        $users= User::filter()
            ->where('role', '=', 'admin')
            ->paginate(20);


        $paginateMetaData= $users->toArray();
        $usersResource= UserResource::collection($users);
        $data= [...$paginateMetaData, 'data'=> $usersResource];
        return $this->success($data);
    }

    // @deprecated
    public function store(StoreAdminUserRequest $request){
        /*$request->validated($request->all());
        $user= User::create([
            'first_name'=> $request->first_name,
            'last_name'=> $request->last_name,
            'role'=> 'admin',
            'admin_role'=> $request->admin_role,
            'email'=> $request->email,
            'password'=> '',
            'country'=> $request->country,
            'phone_number'=> $request->phone_number,
            'phone_dial_code'=> $request->phone_dial_code,
            'email_verified_at'=> now()
        ]);
        $token= $user
        ->createToken('Personal Access Token for '.$request->email)
        ->plainTextToken;

    $resetToken= PasswordResetToken::create([
        'user_id'=> $user->id
    ]);
    event(new UserRegistered($user));
    event(new PasswordTokenCreated($resetToken));*/

    return $this->success(null, 'Admin created successfully');

    }

    public function getOrganizer(User $organizer){
        $organizerData= new OrganizerResource($organizer);

        return $this->success($organizerData);
    }

    public function exportUsersCSV(ExportUsersRequest $request){
        $request->validated($request->all());
        $ids= explode(',',$request->ids);
        $users= User::whereIn('id',$ids )
        ->get();


        $csv = Writer::createFromString('');
        $csv->insertOne([
            'id',
            'name',
            'email',
            'avatar',
            'phone',
            'created_at',
            'role',
            'account_status']);
        $usersCollection= UserResource::collection($users)
            ->toArray($request);

        foreach ($usersCollection as $user){
            $csv->insertOne([
                $user['id'],
                $user['name'],
                $user['email'],
               $user['avatar'],
                $user['phone'],
                $user['created_at'],
                $user['role'],
                $user['account_status']
            ]);
        }

        $headers = [
            'Content-Type'  => 'text/csv',
            'Content-Disposition' => 'attachment; filename="example.csv"',
        ];

        // Convert the CSV to a string
        $csvString = $csv->getContent();
        return response($csvString, 200, $headers);
    }

    public function updateOrganizerSettings(UpdateOrganizerSettingsRequest $request){
        $request->validated($request->all());


        $bankDetails= OrganizerBankDetails::where('user_id', $request->organizer_id)
        ->first();
        $commision= Commision::where('user_id', $request->organizer_id)
        ->first();

        if(!$bankDetails){
            OrganizerBankDetails::create([
                'swift_code'=> $request->swift_code,
                'iban'=> $request->iban,
                'user_id'=> $request->organizer_id
            ]);
        } else{
            $bankDetails->update([
                'swift_code'=> $request->swift_code,
                'iban'=> $request->iban
            ]);
        }



        if(!$commision && $request->commision){
            Commision::create([
                'user_id'=> $request->organizer_id,
                'rate'=> $request->commision
            ]);
        }
        if($commision && $request->commision){
            $commision->update([
                'rate'=> $request->commision
            ]);
        }

        return $this->success(null, 'Organizer settings updated successfully');

    }

    public function activateOrganizer(User $organizer){
        $organizer->update([
            'account_state'=> 'active'
        ]);
        Mail::to($organizer->email)
            ->send(new ActivatedOrganizerMail($organizer));
        return $this->success(null, 'Organizer activated successfully');
    }
    public function deactivateOrganizer(User $organizer){
        $organizer->update([
            'account_state'=> 'blocked'
        ]);

        return $this->success(null, 'Organizer deactivated successfully');
    }

    public function loginAs(User $organizer){
        $token= $organizer
        ->createToken('Personal Access Token for '.$organizer->email)
        ->plainTextToken;

    return $this
        ->success(['access_token'=> $token], 'Logged in successfully');
    }

    public function destroyUser(User $user){
        $user->delete();

        return $this->success(null, 'User deleted successfully');
    }
}
