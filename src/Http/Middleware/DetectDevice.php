<?php

namespace Vtlabs\Core\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Vtlabs\Core\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class DetectDevice
{
    public function __construct()
    {
    }

    public function handle($request, Closure $next)
    {
        $this->createLog($request);

        // continue request
        return $next($request);
    }

    public function createLog($request)
    {
        $deviceId = $request->header("X-Device-Id") ?? null;
        $deviceType = $request->header("X-Device-Type") ?? 'unkown';

        if($deviceId)
        {
            $alreadyLogged = ActivityLog::where('device_id', $deviceId)->whereDate('created_at', '>', Carbon::now()->subHours(1))->exists();

            if(!$alreadyLogged) {
                ActivityLog::create([
                    'device_id' => $deviceId,
                    'device_type' => $deviceType,
                    'user_id' => Auth::id()
                ]);
            }
        }
    }

    public function check($locale)
    {
        if (!$locale) return false;

        return $this->locales->contains($locale);
    }

    protected function detectLocale($request)
    {
        // Check header request and determine localizaton
        $locale = $request->header("X-Localization") ?? $request->route()->parameter('X-Localization');
        return ($this->check($locale)) ? $locale : config('app.fallback_locale');
    }
}
