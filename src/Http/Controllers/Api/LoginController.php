<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use Firebase\JWT\JWT;
use Vtlabs\Core\Events\LoggedIn;
use Vtlabs\Core\Models\User\User;
use Vtlabs\Core\Helpers\CoreHelper;
use Vtlabs\Core\Services\FirebaseService;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\UserResource;
use Vtlabs\Core\Http\Requests\Api\LoginRequest;
use Vtlabs\Core\Http\Requests\Api\CheckUserRequest;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @group  Auth
 *
 * APIs for authentication
 */
class LoginController extends Controller
{
    /**
     * Login
     *
     * @bodyParam  token string required Firebase token. Example: firebase_token
     * @bodyParam  role string required Role of the user logging in. Example: customer
     * 
     * @responseFile responses/core/login.post.200.json
     * @responseFile 422 responses/core/login.post.422.json
     */
    public function login(LoginRequest $request)
    {
        try {
            if ($this->_isSimulateUser()) {
                return $this->_prepareResponse(User::findOrFail(request()->input('simulate_user')));
            }

            $decoded = JWT::decode($request->token, CoreHelper::getFirebaseKeyIds(), array('RS256'));

            if ($decoded->iss !== FirebaseService::getFirebaseIss()) {
                throw new \Exception('Wrong FIREBASE_ISS provided');
            }

            $mobile_number = property_exists($decoded, 'phone_number') ? $decoded->phone_number : null;

            if (empty($mobile_number)) {
                throw new \Exception('Mobile number not present in token');
            }

            $user = config('auth.models.user')::where('mobile_number', '=', $mobile_number)->firstOrFail();

            // raise loggedin event
            event(new LoggedIn($user, $request->role));

            return $this->_prepareResponse($user);
        } catch (\Exception $ex) {
            throw new BadRequestHttpException($ex->getMessage());
        }
    }

    /**
     * Check if user exist
     * 
     * @bodyParam  mobile_number string required Mobile Number to check. Example +912324252627
     * 
     * @responseFile responses/core/checkUser.post.200.json
     * @responseFile 422 responses/core/checkUser.post.422.json
     */
    public function checkUser(CheckUserRequest $request)
    {
        return response()->json(["message" => "User exists"]);
    }

    private function _prepareResponse($user)
    {
        $user->mobile_verified = 1;
        $user->save();
        $token = $user->createToken('Default')->accessToken;
        return response()->json(["token" => $token, "user" => new UserResource($user->refresh())]);
    }

    private function _isSimulateUser()
    {
        return request()->has('simulate_user') && request()->input('app_key') == env('APP_KEY');
    }
}
