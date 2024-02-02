<?php

namespace Vtlabs\Core\Http\Resources\Admin;

use Vtlabs\Core\Http\Resources\UserResource;

class UserAdminResource extends UserResource
{
    public function toArray($request)
    {
        return collect(parent::toArray($request))->merge(['roles' => $this->roles]);
    }
}