<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Vtlabs\Core\Models\User\User;
use Illuminate\Support\Facades\DB;
use Vtlabs\Core\Filters\UserFilter;
use Vtlabs\Core\Helpers\CoreHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Vtlabs\Core\Models\Notification;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\BlockResource;
use Vtlabs\Core\Helpers\PushNotificationHelper;
use Vtlabs\Core\Http\Resources\NotificationResource;

/**
 * @group  User Management
 *
 * APIs for user management
 */
class UserController extends Controller
{
    private $userModel;
    private $userResource;

    public function __construct()
    {
        $this->userModel = config('auth.models.user');
        $this->userResource = config('auth.resources.user');

        if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            $this->middleware('auth:api');
        }
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => 'sometimes'
        ]);

        $users = $this->userModel::filter($request->all(), UserFilter::class);

        return $this->userResource::collection($users->paginate());
    }

    public function show()
    {
        return new $this->userResource(Auth::user());
    }

    public function showUserById(User $user)
    {
        return new $this->userResource($user);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'sometimes',
            'username' => 'sometimes',
            'image_url' => 'sometimes|nullable|url',
            'notification' => 'sometimes|json|nullable',
            'language' => 'sometimes|string',
            'meta' => 'sometimes|json',
            'categories' => 'sometimes|array|exists:categories,id',
            'balance' => 'sometimes|numeric'
        ]);

        $user = Auth::user();

        if ($request->name) {
            $user->name = $request->name;
        }

        if ($request->gender) {
            $user->gender = $request->gender;
        }

        if ($request->dob) {
            $user->dob = $request->dob;
        }

        if ($request->username) {
            $user->username = $request->username;
        }

        if ($request->notification) {
            $user->notification = json_decode($request->notification);
        }

        if ($request->language) {
            $user->language = $request->language;
        }

        if ($request->image_url) {
            $newMediaItems = [];
            $newMediaItems[] = $user->addMediaFromUrl($request->image_url)->toMediaCollection('images');
            $user->clearMediaCollectionExcept('images', $newMediaItems);
        }

        if ($request->meta) {
            $user->meta = json_decode($request->meta);
        }

        $user->save();

        if ($request->categories) {
            $user->categories()->sync($request->categories);
        }

        // update user wallet
        if ($request->balance) {
            if ($request->balance > $user->balance) {
                // if new balance is greater, deposit amount in user wallet
                $user->deposit($request->balance - $user->balance,'deposit',
                [
                    'description' => 'Amount deposited in wallet',
                    'type' => 'deposit'
                ]);
            } else if ($request->balance < $user->balance) {
                // if new balance is lesser, withdraw amount from user wallet
                $user->withdraw($user->balance - $request->balance,'deposit',
                [
                    'description' => 'Amount deducted from wallet',
                    'type' => 'deduct'
                ]);
            }
        }

        return new $this->userResource($user->fresh());
    }

    public function newChatNotification(Request $request)
    {
        $request->validate([
            "role" => "sometimes|role",
            "user_id" => "sometimes|exists:users,id",
            'users' => 'sometimes|array',
            'users.*.role' => 'required',
            'users.*.user_id' => 'required|exists:users,id',
        ]);

        if ($request->user_id) {
            $notifiedUser = $this->userModel::find($request->user_id);

            $notifiedUser->sendPushNotification(
                $request->role,
                __('vtlabs_core::messages.notification_chat_new_message_title'),
                __('vtlabs_core::messages.notification_chat_new_message_body')
            );
        }

        if ($request->users) {
            $notifyIds = [];
            foreach ($request->users as $key => $value) {
                $notifyIds[] = $this->userModel::find($value['user_id'])->notification['customer'];
            }

            $oneSignal = PushNotificationHelper::getOneSignalInstance('customer');

            $data['title'] = __('vtlabs_core::messages.notification_chat_new_message_title');
            $data['body'] = __('vtlabs_core::messages.notification_chat_new_message_body');

            $oneSignal->sendNotificationToUser(
                $data['title'],
                $notifyIds,
                null,
                $data
            );
        }

        return response()->json((object)[], 200);
    }

    public function notifications(Request $request)
    {
        $request->validate([
            'type' => 'sometimes'
        ]);

        $notifications = Notification::where('user_id', Auth::id())->orderByDesc('created_at');

        if($request->upcoming) {
            $notifications = $notifications->where('created_at', '>=', Carbon::now()->toDateString());
        }

        if($request->past) {
            $notifications = $notifications->where('created_at', '<', Carbon::now()->toDateString());
        }

        if ($request->type) {
            $notifications = $notifications->where('type', $request->type);
        }

        return NotificationResource::collection($notifications->paginate());
    }

    public function notificationSummary(Request $request)
    {
        $notificationsCount = Notification::where('user_id', Auth::id())->where('is_read', 0)->count();

        return response(['count' => $notificationsCount]);
    }

    public function readNotifications()
    {
        Notification::where('user_id', Auth::id())->update(['is_read' => 1]);

        return response()->json((object)[], 200);
    }

    public function report(User $user, Request $request)
    {
        $request->validate([
            'reason' => 'sometimes'
        ]);

        $reporter = User::find(Auth::id());

        $user->report($reporter, ['reason' => $request->reason]);

        return response([], 200);
    }

    public function block(User $user, Request $request)
    {
        $request->validate([
            'reason' => 'sometimes'
        ]);

        $blocker = User::find(Auth::id());
        $blocked = false;

        if (Auth::id() != $user->id) {
            if ($blocker->hasBlocked($user)) {
                // unblock user if already blocked
                $blocker->unblock($user);
            } else {
                $blocked = true;
                $blocker->block($user, ['reason' => $request->reason]);
            }
        }

        return response(["block" => $blocked], 200);
    }

    public function blockList(Request $request)
    {
        return BlockResource::collection(Auth::user()->blockedList(User::class)->paginate());
    }

    public function ratingList(User $user, Request $request)
    {
        return DoctorRatingResource::collection($user->raters(User::class)->orderByDesc('pivot_created_at')->paginate());
    }

    public function ratingStore(User $user, Request $request)
    {
        $request->validate([
            'rating' => 'required|numeric',
            'review' => 'required'
        ]);

        $user = Auth::user();

        $user->rate($user, $request->rating, $request->review);

        return response()->json([], 200);
    }

    public function ratingSummary(User $user)
    {
        return response()->json([
            "average_rating" => $user->averageRating(User::class),
            "total_ratings" => $user->raters(User::class)->count(),
            "summary" => DB::table('ratings')->selectRaw('count(*) as total, ROUND(rating) as rounded_rating')
                ->where('rateable_type', User::class)
                ->where('rateable_id', $user->id)
                ->where('rater_type', User::class)
                ->groupBy('rounded_rating')
                ->get()
        ]);
    }

    public function refer(Request $request)
    {
        $request->validate([
            'referral_code' => 'required|exists:users,referral_code'
        ]);

        $result = false;

        $user = User::where('referral_code', $request->referral_code)->first();

        $oldReferralCount = DB::table('referral_logs')->where('user_id', Auth::id())->count();

        if ($user->id != Auth::id() && $oldReferralCount == 0) {
            $settings = CoreHelper::settingsAsDictionary();
            if (isset($settings['referral_amount'])) {
                $user->deposit(
                    $settings['referral_amount'],
                    'deposit',
                    [
                        'description' => 'Amount deposited in wallet for referral',
                        'type' => 'referral',
                        'source' => 'user',
                        'source_id' => Auth::id(), // referrer
                    ]
                );

                // log a referral
                DB::table('referral_logs')->insert(['referred_by' => $user->id, 'user_id' => Auth::id()]);
                $result = true;
            }
        }

        return response()->json(['result' => $result], $result ? 200 : 400);
    }

    public function destroy()
    {
        $user = Auth::user();
        $user->delete();

        return response()->json([], 200);
    }
}
