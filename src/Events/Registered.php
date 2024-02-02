<?php

namespace Vtlabs\Core\Events;

use Illuminate\Queue\SerializesModels;
use PhpParser\Node\Scalar\String_;

class Registered
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
    public $roles;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string
     * @return void
     */
    public function __construct($user, $roles)
    {
        $this->user = $user;
        $this->roles = $roles;
    }
}
