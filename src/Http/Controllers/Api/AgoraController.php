<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Helpers\Agora\RtcTokenBuilder;
use Vtlabs\Core\Helpers\Agora\RtmTokenBuilder;

class AgoraController extends Controller
{
    public function token(Request $request)
    {
        $request->validate([
            'channel' => 'sometimes',
            'uid' => 'sometimes',
            'token_type' => 'required'
        ]);

        $appID = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');
        $channelName = $request->channel;
        $uid = $request->uid;
        $uidStr = $request->uid;
        $role = RtcTokenBuilder::RoleAttendee;
        $expireTimeInSeconds = 90000;
        $currentTimestamp = (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        if($request->token_type == 'rtc') {            
            $token = RtcTokenBuilder::buildTokenWithUserAccount($appID, $appCertificate, $channelName, $uidStr, RtcTokenBuilder::RoleAttendee, $privilegeExpiredTs);
        }

        if($request->token_type == 'rtm') {
            $token = RtmTokenBuilder::buildToken($appID, $appCertificate, $uidStr, RtmTokenBuilder::RoleRtmUser, $privilegeExpiredTs);
        }

        return response()->json(['token' => $token]);
    }
}
