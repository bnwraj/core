<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use Google_Client;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Vtlabs\Core\Models\User\User;
use Vtlabs\Core\Helpers\CoreHelper;
use Vtlabs\Core\Services\FirebaseService;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\UserResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @group Auth
 *
 * APIs for authentication
 */

class SocialLoginController extends Controller
{
    private $_token;

    private $_platform;

    private $_os;

    private $_role;

    /**
     * Social Login
     *
     * @bodyParam platform string required Platform. Example: google,facebook,apple
     * @bodyParam token string required Token. Example: social_token
     * @bodyParam os string required Current OS. Example: android, ios
     * @bodyParam role string required Role fo the user logging in. Example: customer
     * 
     * @responseFile responses/core/login.post.200.json
     * @responseFile 400 responses/core/social_login.post.400.json
     * @responseFile 404 responses/core/social_login.post.404.json
     * @responseFile 422 responses/core/social_login.post.422.json
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'platform' => 'required|in:google,facebook,apple',
            'token' => 'required',
            'os' => 'required|in:android,ios',
            'role' => 'required'
        ]);

        $this->_token = $request->token;
        $this->_platform = $request->platform;
        $this->_os = $request->os;
        $this->_role = $request->role;

        try {
            $email = null;
            $name = null;

            if ($this->_platform == 'google') {
                $data = $this->_googleLogin($request->token);
                $email = $data['email'];
                $name = $data['name'];
            }

            if ($this->_platform == 'facebook') {
                $data = $this->_facebookLogin($request->token);
                $email = $data['email'];
                $name = $data['name'];
            }

            if ($this->_platform == 'apple') {
                $email = $this->_appleLogin($request);
            }

            if ($email == null) {
                return response()->json(["message" => 'Email not found from token'], 400);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(["message" => 'User does not exist', "name" => $name, "email" => $email], 404);
            }

            $token = $user->createToken('Default')->accessToken;
            return response()->json(["token" => $token, "user" => new UserResource($user->refresh())]);
        } catch (\Exception $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
    }

    private function _googleLogin()
    {
        $client_id_env_key = $this->_getGoogleClientIdKey($this->_os, $this->_role);
        $client_id = env($client_id_env_key, null);

        if ($client_id == null) {
            throw new \Exception('Google Client ID not configured on server');
        }

        $client = new Google_Client(['client_id' => $client_id]);  // Specify the CLIENT_ID of the app that accesses the backend
        $payload = $client->verifyIdToken($this->_token);
        if ($payload) {
            return ['email' => $payload['email'], 'name'  => $payload['name']];
        }
        throw new \Exception('Invalid Google Token');
    }

    private function _facebookLogin()
    {
        // https://github.com/facebook/php-graph-sdk
        $app_id = env('FACEBOOK_APP_ID', null);
        $app_secret = env('FACEBOOK_APP_SECRET', null);

        $fb = new \Facebook\Facebook([
            'app_id' => $app_id,
            'app_secret' => $app_secret,
            'default_graph_version' => 'v2.10'
        ]);

        try {
            $response = $fb->get('/me?fields=name,email', $this->_token);
            $me = $response->getGraphUser();
            return ["email" => $me->getEmail(), "name"  => $me->getName()];
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            throw new \Exception('Graph returned an error: ' . $e->getMessage());
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            throw new \Exception('Facebook SDK returned an error: ' . $e->getMessage());
        }
    }

    private function _appleLogin()
    {
        try {
            $decoded = JWT::decode($this->_token, CoreHelper::getFirebaseKeyIds(), array('RS256'));

            if ($decoded->iss !== FirebaseService::getFirebaseIss()) {
                throw new \Exception('ISS mismatch');
            }

            return $decoded->email;
        } catch (\Exception $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
    }

    private function _getGoogleClientIdKey($os, $role)
    {
        $client_id_env_key = $os == 'android' ? 'GOOGLE_CLIENT_ID' : 'GOOGLE_IOS_CLIENT_ID';
        if ($os == 'ios' && $role == 'provider') {
            $client_id_env_key = 'GOOGLE_IOS_PROVIDER_CLIENT_ID';
        }
        return $client_id_env_key;
    }
}
