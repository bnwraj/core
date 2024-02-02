<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Core\Models\AdminPermission;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\Admin\UserAdminResource;

class LoginController extends Controller
{
    /**
     * Login admin user
     *
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($this->validate($request) || Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            // if(!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('manager') && !Auth::user()->hasRole('eventmanager')) {
            //     return response()->json(["message" => "Permission denied. No suitable role found"], 400);
            // }
            $user = Auth::user();
            $token = $user->createToken('Default')->accessToken;

            // permission
            $permissions = AdminPermission::where('role', $user->roles()->first()->name)->first();
            
            return response()->json(["token" => $token, "user" => new UserAdminResource($user), 'permissions' => $permissions]);
        }
        return response()->json(["message" => "Invalid Login"], 400);
    }
}
