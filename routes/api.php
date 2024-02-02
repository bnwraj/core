<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


use Illuminate\Http\Request;

Route::middleware('api')->prefix('api')->name('api')->namespace('Vtlabs\Core\Http\Controllers\Api')->group(function () {
    // for admin
    Route::namespace('Admin')->name('admin')->prefix('admin')->group(function () {
        Route::post('/login', 'LoginController@authenticate');

        Route::get('/config/languages', 'ConfigurationController@languages');

        Route::get('/download/user', 'UserController@export');

        Route::middleware('auth:api')->group(function () {
            Route::get('users/roles', 'UserController@roles');
            Route::get('users/reports', 'UserController@reports');
            Route::delete('users/reports/{report}', 'UserController@deleteReport');
            Route::apiResource('users', 'UserController');

            Route::apiResource('mobilelanguages', 'MobileLanguageController');

            Route::get('/wallet/transactions', 'WalletController@transactions');
            Route::get('/wallet/transactions/{transaction}', 'WalletController@showTransaction');
            Route::put('/wallet/transactions/{transaction}', 'WalletController@updateTransaction');
            Route::put('/wallet/rejecttransactions/{transaction}', 'WalletController@rejectTransaction');
            

            // settings
            Route::get('/settings', 'SettingController@index');
            Route::post('/settings', 'SettingController@update');
            Route::get('/settings/env', 'SettingController@envList');
            Route::post('/settings/env', 'SettingController@updateEnv');

            // dashboard
            Route::get('/dashboard/user-analytics', 'DashboardController@userAnalytics');
            Route::get('/dashboard/transaction-analytics', 'DashboardController@transactionAnalytics');
            Route::get('/dashboard/activity-analytics', 'DashboardController@activityAnalytics');

            // plan
            Route::apiResource('plans', 'PlanController');

            // admin permission
            Route::apiResource('permissions', 'AdminPermissionController');
        });
    });

    Route::middleware('auth:api')->group(function () {
        Route::get('user', 'UserController@show');
        Route::put('user', 'UserController@update');
        Route::delete('user', 'UserController@destroy');

        Route::get('user/notifications', 'UserController@notifications');
        Route::post('user/notifications/read', 'UserController@readNotifications');
        Route::get('user/notifications/summary', 'UserController@notificationSummary');

        Route::post('/user/push-notification', 'UserController@newChatNotification')->name('user.newChatNotification');

        Route::post('agora/token', 'AgoraController@token');

        // wallet
        Route::get('user/wallet/balance', 'WalletController@checkBalance');
        Route::post('user/wallet/deposit', 'WalletController@deposit');
        Route::get('user/wallet/transactions', 'WalletController@transactions');
        Route::post('user/wallet/payout', 'WalletController@payout');
        Route::get('user/wallet/earnings', 'WalletController@earningsSummary');

        // follow
        Route::post('user/follow/{user}', 'FollowController@toggleFollow');
        Route::get('user/followers/{user}', 'FollowController@followers');
        Route::get('user/following/{user}', 'FollowController@following');

        // list of users
        Route::get('user/list', 'UserController@index');

        // report
        Route::post('user/report/{user}', 'UserController@report');

        // block
        Route::get('user/block', 'UserController@blockList');
        Route::post('user/block/{user}', 'UserController@block');

        // address
        Route::get('user/addresses', 'AddressController@index');
        Route::post('user/addresses', 'AddressController@store');
        Route::put('user/addresses/{address}', 'AddressController@update');
        Route::delete('user/addresses/{address}', 'AddressController@destroy');

        // rating

        Route::get('user/ratings/{user}', 'UserController@ratingList');
        Route::post('user/ratings/{user}', 'UserController@ratingStore');

        // refer
        Route::post('user/refer', 'UserController@refer');
    });

    Route::get('user/{user}', 'UserController@showUserById');
    Route::get('user/follow/stats/{user}', 'FollowController@stats');

    Route::post('check-user', 'LoginController@checkUser')->name('checkUser');
    Route::post('login', 'LoginController@login')->name('login');
    Route::post('register', 'RegisterController@register')->name('register');
    Route::post('verify-mobile', 'RegisterController@verifyMobile')->name('verifyMobile');
    Route::post('social/login', 'SocialLoginController@authenticate')->name('social.authenticate');

    Route::get('settings', 'SettingController@index');

    Route::get('mobilelanguages', 'MobileLanguageController@index');
});
