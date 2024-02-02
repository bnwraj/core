<?php

namespace Vtlabs\Core\Models;

class PushNotification
{
    public $locale;
    public $role;
    public $title;
    public $body;
    public $notificationId;
    public $data;

    public function __construct($locale, $role, $title, $body, $notificationId, $data)
    {
        $this->locale = $locale;
        $this->role = $role;
        $this->title = $title;
        $this->body = $body;
        $this->notificationId = is_array($notificationId) ? $notificationId : array($notificationId);
        $this->data = $data;
    }
}