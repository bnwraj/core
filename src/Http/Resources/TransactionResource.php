<?php

namespace Vtlabs\Core\Http\Resources;

use Vtlabs\Core\Models\User\User;
use Vtlabs\Core\Http\Resources\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource(User::find($this->wallet->user_id))
        ];
    }
}