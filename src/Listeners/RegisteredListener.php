<?php

namespace Vtlabs\Core\Listeners;

use Vtlabs\Core\Events\Registered;
use Vtlabs\Core\Events\RoleAssigned;

class RegisteredListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        event(new RoleAssigned($event->user, $event->roles));
        return true;
    }
}
