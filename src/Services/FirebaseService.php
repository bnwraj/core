<?php

namespace Vtlabs\Core\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class FirebaseService
{
    public static function getDatabaseInstance()
    {
        $instance = null;
        try {
            $instance = (new Factory)->withServiceAccount(base_path() . '/' . env('FIREBASE_SERVICE_ACCOUNT_JSON', 'firebase.json'));

            if (env('FIREBASE_RTDB_URI')) {
                $instance = $instance->withDatabaseUri(env('FIREBASE_RTDB_URI'));
            } else if (env('FIREBASE_USE_NEW_RTDB_URI')) {
                $instance = $instance->withDatabaseUri(\sprintf("https://%s-default-rtdb.firebaseio.com", env('FIREBASE_ID')));
            }

            $instance = $instance->createDatabase();
        } catch (\Exception $ex) {
            //
        }

        return $instance;
    }

    public static function getFirebaseIss()
    {
        if (env('FIREBASE_ID')) {
            return "https://securetoken.google.com/" . env('FIREBASE_ID');
        }

        return env('FIREBASE_ISS'); // FIREBASE_ISS is for backward compatibility
    }
}
