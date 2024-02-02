<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vtlabs\Core\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\UserResource;
use Vtlabs\Core\Http\Requests\Api\RegisterRequest;

/**
 * @group  Auth
 *
 * APIs for authentication
 */
class RegisterController extends Controller
{
    /**
     * Register
     *
     * @bodyParam  name string required Name of user. Example: John
     * @bodyParam  email string required Email of user. Example: john@example.com
     * @bodyParam  mobile_number string required Mobile Number of user. Example: +912324252627
     * @bodyParam  image_url string Image url for profie picture. Example: https://via.placeholder.com/50
     * @bodyParam  role string required Role of user. Example: customer
     * 
     * @responseFile responses/core/register.post.200.json
     * @responseFile 422 responses/core/register.post.422.json
     */
    public function register(RegisterRequest $request)
    {
        request()->merge(['meta' => $request->meta ? json_decode($request->meta) : null]);
        
        $user = $this->create($request->all());

        // image
        if ($request->image) {
            $user->addMediaFromUrl($request->image)->toMediaCollection("images");
        }

        // attach role
        $user->assignRole($request->role);

        event(new Registered($user, [$request->role]));

        return response()->json(["token" => $user->createToken('Default')->accessToken, "user" => new UserResource(config('auth.models.user')::find($user->id))]);
    }

    /**
     * Verifies user's mobile
     *
     * @bodyParam  mobile_number string required Mobile Number to verify. Example: +912324252627
     * 
     * @responseFile responses/core/register.post.200.json
     * @responseFile responses/core/verify.post.422.json
     */
    public function verifyMobile(Request $request)
    {
        Validator::make($request->all(), [
            'mobile_number' => 'required|string|exists:users,mobile_number'
        ])->validate();

        $user = config('auth.models.user')::where('mobile_number', $request->mobile_number)->first();
        $user->mobile_verified = 1;
        $user->save();

        return response()->json(["token" => $user->createToken('Default')->accessToken, "user" => new UserResource(config('auth.models.user')::find($user->id))]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     */
    protected function create(array $data)
    {
        return config('auth.models.user')::create([
            'name' => $data['name'],
            'email' => !empty($data['email']) ? $data['email'] : null,
            'password' => bcrypt(uniqid()),
            'mobile_number' => $data['mobile_number'],
            'meta' => $data['meta']
        ]);
    }
}
