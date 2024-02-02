<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Vtlabs\Report\Models\Report;
use Vtlabs\Core\Models\User\User;
use Spatie\Permission\Models\Role;
use Vtlabs\Core\Events\Registered;
use Vtlabs\Core\Filters\UserFilter;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Rennokki\Plans\Models\PlanModel;
use Vtlabs\Core\Events\RoleAssigned;
use Vtlabs\Core\Exports\UsersExport;
use Vtlabs\Core\Services\UserService;
use Illuminate\Support\Facades\Storage;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\Admin\UserAdminResource;
use Vtlabs\Core\Http\Resources\Admin\ReportedUserAdminResource;

class UserController extends Controller
{
    private $userModel;
    public function __construct()
    {
        $this->userModel = config('auth.models.user');
    }

    public function index(Request $request)
    {
        $request->validate([
            'pagination' => 'sometimes|boolean',
            'email' => 'sometimes',
            'name' => 'sometimes',
            'mobile_number' => 'sometimes'
        ]);

        $users = $this->userModel::filter($request->all(), UserFilter::class);

        if ($request->pagination == '0') {
            $users = $users->get();
        } else {
            $users = $users->paginate();
        }

        return UserAdminResource::collection($users);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'mobile_number' => 'required|unique:users',
            'password' => 'required|min:6',
            'mobile_verified' => 'required|boolean',
            'is_verified' => 'sometimes|boolean',
            'username' => 'sometimes|max:255',
            'image' => 'sometimes|image',
            'language' => 'required|locale',
            'roles' => ['required', 'array', 'role'],
            'meta' => 'sometimes|json|nullable',
            'balance' => 'sometimes|numeric',
            'plan_id' => 'sometimes|exists:plans,id'
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        request()->merge([
            "password" => bcrypt($request->password)
        ]);

        $user = UserService::create(collect($request->only(['name', 'email', 'mobile_number', 'password', 'mobile_verified', 'language', 'roles', 'username', 'meta', 'is_verified','dob' ,'gender'])));

        if ($request->image) {
            $user->addMedia($request->image)->toMediaCollection("images");
        }

        $user->save();

        // update user wallet
        if ($request->balance) {
            if ($request->balance > $user->balance) {
                // if new balance is greater, deposit amount in user wallet
                $user->deposit($request->balance - $user->balance,
                'deposit',
                [
                    'description' => 'Amount deposited in wallet',
                    'type' => 'deposit'
                ]);
            } else if ($request->balance < $user->balance) {
                // if new balance is lesser, withdraw amount from user wallet
                $user->withdraw($user->balance - $request->balance,
                'deposit',
                [
                    'description' => 'Amount deducted from wallet',
                    'type' => 'deduct'
                ]);
            }
        }

        // plan assignment
        if ($request->plan_id) {
            $plan = PlanModel::find($request->plan_id);
            if ($user->hasActiveSubscription()) {
                $user->cancelCurrentSubscription();
            }
            $user->subscribeTo($plan, $plan->duration);
        }

        return new UserAdminResource($user->fresh());
    }

    public function show($id)
    {
        return new UserAdminResource($this->userModel::find($id));
    }

    public function update(Request $request, $id)
    {
        $user = $this->userModel::findOrFail($id);

        $request->validate([
            'name' => 'required|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'mobile_number' => ['required', Rule::unique('users')->ignore($user->id)],
            'mobile_verified' => 'required|boolean',
            'is_verified' => 'sometimes|boolean',
            'dob' => 'sometimes|date',
            'gender' => 'sometimes|string',
            'password' => 'sometimes|min:6',
            'username' => 'sometimes|max:255',
            'image' => 'sometimes|image',
            'language' => 'required|locale',
            'roles' => ['required', 'array', 'role'],
            'meta' => 'sometimes|json|nullable',
            'balance' => 'sometimes|numeric'
        ]);

        if ($request->has('password')) {
            request()->merge([
                "password" => bcrypt($request->password)
            ]);
        }

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $user = $user->fill($request->only(['name', 'email', 'mobile_number', 'password', 'mobile_verified', 'language', 'username', 'meta', 'is_verified','dob','gender']));

        if ($request->image) {
            $user->clearMediaCollection('images');
            $user->addMedia($request->image)->toMediaCollection("images");
        }

        $user->save();

        // attach roles
        $user->syncRoles($request->roles);

        event(new RoleAssigned($user, $request->roles));

        // update user wallet
        if ($request->balance) {
            if ($request->balance > $user->balance) {
                // if new balance is greater, deposit amount in user wallet
                $user->deposit($request->balance - $user->balance,'deposit',
                [
                    'description' => 'Amount deposited in wallet',
                    'type' => 'deposit'
                ]);
            } else if ($request->balance < $user->balance) {
                // if new balance is lesser, withdraw amount from user wallet
                $user->withdraw($user->balance - $request->balance,'deposit',
                [
                    'description' => 'Amount deducted from wallet',
                    'type' => 'deduct'
                ]);
            }
        }

        // plan assignment
        if ($request->plan_id) {
            $plan = PlanModel::find($request->plan_id);
            if ($user->hasActiveSubscription()) {
                $user->cancelCurrentSubscription();
            }
            $user->subscribeTo($plan, $plan->duration);
        }

        return new UserAdminResource($user->fresh());
    }

    public function destroy($id)
    {
        Gate::authorize('delete');

        $this->userModel::findOrFail($id)->delete();

        return response()->json([], 200);
    }

    public function roles()
    {
        return response()->json(Role::all());
    }

    public function reports()
    {
        $reports = Report::query()->orderByDesc('created_at')->paginate();
        return ReportedUserAdminResource::collection($reports);
    }

    public function deleteReport(Report $report)
    {
        $report->delete();
        return response()->json([], 200);
    }

    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }
}
