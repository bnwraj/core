<?php

namespace Vtlabs\Core\Events;

use Illuminate\Queue\SerializesModels;

class LoggedIn
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
     * @var string
     */
    public $role;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string
     * @return void
     */
    public function __construct($user, $role)
    {
        $this->user = $user;
        $this->role = $role;
    }
}
