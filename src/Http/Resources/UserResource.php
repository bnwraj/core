<?php

namespace Vtlabs\Core\Http\Resources;

use Vtlabs\Core\Models\User\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Category\Http\Resources\CategoryResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'mobile_number' => $this->mobile_number,
            'mobile_verified' => $this->mobile_verified,
            'is_verified' => $this->is_verified,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'active' => $this->active,
            'language' => $this->language,
            'notification' => $this->notification,
            'meta' => $this->meta,
            'mediaurls' => $this->getMediaUrlsAttribute(),
            'balance' => $this->balance,
            'wallet' => $this->wallet,
            'is_following' => Auth::check() ? Auth::user()->isFollowing($this->resource) : false,
            'categories' => CategoryResource::collection($this->categories),
            'plan' => $this->hasActiveSubscription() ? $this->activeSubscription() : null,
            'is_blocked' => Auth::check() ? Auth::user()->hasBlocked($this->resource) : false,
            'ratings' => $this->averageRating(User::class),
            'ratings_count' => $this->raters(User::class)->count(),
            'referral_code' => $this->referral_code
        ];
    }
    
}