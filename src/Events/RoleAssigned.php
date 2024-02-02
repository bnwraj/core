<?php

namespace Vtlabs\Core\Events;

use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use SerializesModels;

    /**
     * The authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Role assigned to new user
     *
     * @var array
     */
    public $roles;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string
     * @param  array
     * @return void
     */
    public function __construct($user, $roles)
    {
        $this->user = $user;
        $this->roles = $roles;
    }
}
