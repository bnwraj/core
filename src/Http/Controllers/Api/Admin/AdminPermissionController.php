<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Vtlabs\Core\Models\AdminPermission;
use Vtlabs\Core\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;


class AdminPermissionController extends Controller
{
    public function index(Request $request)
    {
        $adminPermissions = AdminPermission::where('role', '<>', 'administrator');

        return response()->json($adminPermissions->paginate());
    }

    public function show($id)
    {
        $adminPermission = AdminPermission::find($id);

        return response()->json($adminPermission);
    }

    public function store(Request $request)
    {
        $request->validate([
            'role' => 'sometimes|exists:roles,name',
            'new_role' => 'sometimes',
            'permissions' => 'required|array',
            'meta' => 'sometimes|json|nullable',
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta, true)
            ]);
        }

        request()->merge([
            "permissions" => implode(',', request()->input('permissions'))
        ]);

        if ($request->new_role) {
            $role = Role::create(['name' => $request->new_role]);
            request()->merge([
                "role" => $role->name
            ]);
        }

        if(AdminPermission::where('role', $request->role)->exists()) {
            throw ValidationException::withMessages(['role' => 'Permission for ' . $request->role . ' already exists']);
        }

        $adminPermission = AdminPermission::create($request->only(['role', 'permissions', 'meta']));

        return response()->json($adminPermission);
    }

    public function update($id, Request $request)
    {
        $adminPermission = AdminPermission::find($id);
        
        $request->validate([
            'permissions' => 'required|array',
            'meta' => 'sometimes|json|nullable',
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        request()->merge([
            "permissions" => implode(',', request()->input('permissions'))
        ]);

        $adminPermission->fill($request->only(['role', 'permissions', 'meta']));

        $adminPermission->save();

        return response()->json($adminPermission);
    }

    public function destroy($id)
    {
        $adminPermission = AdminPermission::find($id);
        $adminPermission->delete();

        return response()->json([], 204);
    }
}
