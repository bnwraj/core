<?php

namespace Vtlabs\Core\Models\User;

use Illuminate\Support\Str;
use Depsimon\Wallet\HasWallet;
use EloquentFilter\Filterable;
use Rennokki\Rating\Traits\Rate;
use Vtlabs\Payment\Traits\CanPay;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Rennokki\Plans\Traits\HasPlans;
use Vtlabs\Payment\Contracts\Payer;
use Rennokki\Rating\Contracts\Rating;
use Overtrue\LaravelFollow\Followable;
use Overtrue\LaravelLike\Traits\Liker;
use Rennokki\Rating\Traits\CanBeRated;
use Spatie\Permission\Traits\HasRoles;
use Vtlabs\Report\Traits\CanBeBlocked;
use Vtlabs\Report\Traits\CanBeReported;
use Illuminate\Notifications\Notifiable;
use Vtlabs\Core\Models\PushNotification;
use Vtlabs\Appointment\Traits\CanAppoint;
use Vtlabs\Core\Traits\CoreHasMediaTrait;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Vtlabs\Core\Jobs\SendPushNotification;
use Vtlabs\Appointment\Contracts\Appointer;
use BeyondCode\Comments\Contracts\Commentator;
use Illuminate\Foundation\Auth\User as Authenticatable;
use ChristianKuri\LaravelFavorite\Traits\Favoriteability;
use Illuminate\Contracts\Translation\HasLocalePreference;

class User extends Authenticatable implements HasMedia, HasLocalePreference, Rating, Payer, Appointer, Commentator
{
    use Notifiable,
        HasRoles,
        HasApiTokens,
        Filterable,
        Favoriteability,
        CoreHasMediaTrait,
        CanPay,
        Rate,
        CanAppoint,
        HasWallet,
        Followable,
        Liker,
        HasPlans,
        CanBeReported,
        CanBeBlocked;

    protected $table = 'users';

    protected $guard_name = 'api';

    protected $fillable = [
        'name', 'dob','gender','email', 'password', 'mobile_number', 'mobile_verified',
        'active', 'language', 'notification', 'meta', 'is_verified'
    ];

    protected $hidden = ['roles', 'password'];

    protected $casts = [
        'notification' => 'array',
        'meta' => 'array'
    ];

    protected $with = ['roles', 'wallet', 'media', 'followings', 'categories'];

    public $availableMediaConversions = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // $this->availableMediaConversions = [
        //     'thumb' => ["width" => config('vtlabs_core.images.thumb', 50)],
        //     'small' => ["width" => config('vtlabs_core.images.small', 150)]
        // ];
    }

    public static function boot()
    {
        parent::boot();

        // on create
        static::created(function ($user) {
            $user->wallet()->create();

            // create a unique referral code for the user
            while (1) {
                $referralCode = Str::random(8);
                if (!User::where('referral_code', $referralCode)->exists()) {
                    $user->referral_code = $referralCode;
                    $user->save();
                    break;
                }
            }
        });
    }

    /**
     * Check if a comment for a specific model needs to be approved.
     * @param mixed $model
     * @return bool
     */
    public function needsCommentApproval($model): bool
    {
        return false;
    }

    /**
     * Get the user's preferred locale.
     *
     * @return string
     */
    public function preferredLocale()
    {
        return $this->language ?? 'en';
    }

    public function categories()
    {
        return $this->belongsToMany(config('vtlabs_category.models.category'), 'category_preferences', 'user_id', 'category_id');
    }

    public function sendPushNotification($role, $title, $body, $data = [])
    {
        $notificationId = $this->notification[$role] ?? null;

        if (!$notificationId) {
            Log::warning('Push Notification: Missing notfication id', ['role' => $role, 'userId' => $this->id]);
            return false;
        }

        $pushNotification = new PushNotification($this->preferredLocale(), $role, $title, $body, $notificationId, $data);

        config('queue.use_queue') ? SendPushNotification::dispatch($pushNotification) : SendPushNotification::dispatchAfterResponse($pushNotification);
    }
}
