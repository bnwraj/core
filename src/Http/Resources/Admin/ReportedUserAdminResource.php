<?php

namespace Vtlabs\Core\Http\Resources\Admin;

use Vtlabs\Core\Models\User\User;
use Vtlabs\Core\Http\Resources\UserResource;

class ReportedUserAdminResource extends UserResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'reason' => $this->reason,
            'reporter' => new UserResource(User::find($this->reporter_id)),
            'reportable' => new UserResource(User::find($this->reportable_id)),
            'created_at' => $this->created_at
        ];
    }
}
