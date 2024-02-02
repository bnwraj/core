<?php

namespace Vtlabs\Core\Http\Resources;

use Vtlabs\Core\Models\User\User;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'blocked' => new UserResource(User::find($this->blockable_id))
        ];
    }
}