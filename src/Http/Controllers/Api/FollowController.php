<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vtlabs\Core\Models\User\User;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Core\Models\Notification;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\UserResource;

/**
 * @group  User Management
 *
 * APIs for user management
 */
class FollowController extends Controller
{
    public function show()
    {
        return new $this->userResource(Auth::user());
    }

    public function stats(User $user)
    {
        return response()->json([
            'followers_count' => $user->followers->count(),
            'following_count' => $user->followings->count()
        ]);
    }

    public function followers(User $user)
    {
        return response()->json(UserResource::collection($user->followers));
    }

    public function following(User $user)
    {
        return response()->json(UserResource::collection($user->followings));
    }

    public function toggleFollow(User $user)
    {
        $currentUser = Auth::user();
        $currentUser->toggleFollow($user);

        $following = $currentUser->isFollowing($user);

        if ($following) {
            Notification::create([
                'type' => 'follow',
                'text' => 'start following you',
                'user_id' => $user->id,
                'from_user_id' => Auth::id()
            ]);

            $user->sendPushNotification(
                'customer',
                __('vtlabs_core::messages.notification_follow_title', ['follow_by' => Auth::user()->name]),
                __('vtlabs_core::messages.notification_follow_body')
            );
        }

        return response()->json(["follow" => $following]);
    }
}
