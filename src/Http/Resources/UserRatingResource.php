<?php

namespace Vtlabs\Core\Http\Resources;

use App\User;
use Vtlabs\Doctor\Models\DoctorProfile;
use Vtlabs\Core\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Vtlabs\Doctor\Http\Resources\DoctorProfileResource;

class UserRatingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'rating' => $this->pivot->rating,
            'review' => $this->pivot->review,
            'created_at' => $this->pivot->created_at,
            'rated_by' => new UserResource($this->resource),
            'rated' => new UserResource(User::find($this->pivot->rateable_id))
        ];
    }
}