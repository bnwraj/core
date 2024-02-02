<?php

namespace Vtlabs\Core\Http\Middleware;

use Closure;

class DetectLocale
{
    protected $locales;

    public function __construct()
    {
        $this->locales = collect(explode(',', config('app.locales')))->push(config('app.locale'));
    }

    public function handle($request, Closure $next)
    {
        $locale = $this->detectLocale($request);
    
        // set laravel localization
        app()->setLocale($locale);

        // continue request
        return $next($request);
    }

    public function check($locale)
    {
        if (!$locale) return false;

        return $this->locales->contains($locale);
    }

    protected function detectLocale($request)
    {
        // Check header request and determine localizaton
        $locale = $request->header("X-Localization") ?? $request->query->get('X-Localization');
        return ($this->check($locale)) ? $locale : config('app.fallback_locale');
    }
}
