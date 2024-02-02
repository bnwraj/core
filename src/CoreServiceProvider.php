<?php

namespace Vtlabs\Core;

use Laravel\Passport\Passport;
use Vtlabs\Core\Models\User\User;
use Illuminate\Support\Facades\Gate;
use Vtlabs\Core\Models\User\UserPolicy;
use Illuminate\Support\Facades\Validator;
use Vtlabs\Core\Policies\Backend\BackendPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        /**
         * Models Policies
         */
        User::class => UserPolicy::class,
        /**
         * Without models policies
         */
        'backend' => BackendPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        $this->publishes([
            __DIR__.'/../config/auth.php' => config_path('auth.php'),
            __DIR__.'/../config/vtlabs_core.php' => config_path('vtlabs_core.php'),
        ], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // assets
        $this->publishes([
            __DIR__.'/../assets' => public_path('assets/vendor/core/'),
        ], 'public');

        // translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'vtlabs_core');
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/vtlabs_core'),
        ], 'translations');

        // api responses
        $this->publishes([
            __DIR__.'/../storage/responses' => storage_path('responses'),
        ], 'api_response');

        // Implicitly grant "superadmin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            if($user->hasRole('_superadmin')) {
                return true;
            }
        });

        Gate::define('delete', function ($user) {
            return !env('DEMO_ENABLED');
        });

        // Validators
        $this->_registerValidators();
    }

    private function _registerValidators()
    {
        Validator::extend('translation', 'Vtlabs\Core\Validators\TranslationValidator@validate');
        Validator::replacer('translation', 'Vtlabs\Core\Validators\TranslationValidator@replacer');

        Validator::extend('locale', 'Vtlabs\Core\Validators\LocaleValidator@validate');
        Validator::replacer('locale', 'Vtlabs\Core\Validators\LocaleValidator@replacer');

        Validator::extend('role', 'Vtlabs\Core\Validators\RoleValidator@validate');
        Validator::replacer('role', 'Vtlabs\Core\Validators\RoleValidator@replacer');

        Validator::extend('slug', function ($attribute, $value, $parameters, $validator) {
            return $value === str_slug($value);
        });
    }
}
