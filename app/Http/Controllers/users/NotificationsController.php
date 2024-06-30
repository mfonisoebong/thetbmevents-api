<?php

namespace App\Http\Controllers\users;

use App\Models\Notification;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;

class NotificationsController extends Controller
{
    use HttpResponses;
    public function getNotifications(Request $request){

        $notifications= NotificationResource::collection(
            $request->user()->notifications ?? []
        );

        return $this->success($notifications);
    }

    public function readNotification(Notification $notification){
        $notification->update([
            'unread'=> false
        ]);
        return $this->success(null, 'Read notification');
    }

    public function readAll(Request $request){
        $userId= $request->user()
        ->id;
        Notification::where('user_id', '=', $userId)
        ->update([
            'unread'=> false
        ]);
        return $this->success(null, 'All notifications marked as read');
    }

    public function destroyAll(Request $request){
        $notifications= $request->user()->notifications;

        Notification::destroy($notifications);

        return $this->success(null, 'Deleted all notifications');
    }

    public function destroy(Notification $notification, Request $request){
        $user= $request->user();

        if($user->id !== $notification->user_id){
            return $this->failed(403);
        };

        $notification->delete();

        return $this->success(null, 'Notification deleted successfully');
    }
}
