<?php

namespace Vtlabs\Core\Http\Resources;

use Vtlabs\Core\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'meta' => $this->meta,
            'type' => $this->type,
            'user' => new UserResource($this->user),
            'fromuser' =>  new UserResource($this->fromuser),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}