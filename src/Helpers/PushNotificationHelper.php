<?php

namespace Vtlabs\Core\Helpers;

use Berkayk\OneSignal\OneSignalClient;

class PushNotificationHelper
{   
    static function getOneSignalInstance($role) {
        $appId = env('ONESIGNAL_APP_ID_' . strtoupper($role), ""); 
        $restApiKey = env('ONESIGNAL_REST_API_' . strtoupper($role), ""); 

        if(!$appId || !$restApiKey) {
            return false;
        }

        return new OneSignalClient($appId, $restApiKey, "");
        
    }
}
