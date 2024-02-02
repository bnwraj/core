<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Vtlabs\Core\Helpers\CoreHelper;
use Illuminate\Support\Facades\Gate;
use Rennokki\Plans\Models\PlanModel;
use Vtlabs\Core\Http\Controllers\Controller;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'pagination' => 'sometimes|boolean',
        ]);

        $plans = PlanModel::with('features');

        if ($request->pagination == '0') {
            $plans = $plans->get();
        } else {
            $plans = $plans->paginate();
        }

        return response()->json($plans);
    }

    public function show(PlanModel $plan, Request $request)
    {
        return response()->json($plan->load(['features']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'sometimes',
            'price' => 'required|numeric',
            'metadata' => 'sometimes|json|nullable',
            'duration' => 'required|numeric',
            'sort_order' => 'sometimes'
        ]);

        $settings = CoreHelper::settingsAsDictionary();

        request()->merge([
            'currency' => $settings['currency_code'] ?? 'USD'
        ]);

        $plan = PlanModel::create($request->only(['name', 'description', 'price', 'currency', 'duration', 'sort_order', 'metadata']));

        return response()->json($plan);
    }

    public function update(PlanModel $plan, Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'sometimes',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
            'metadata' => 'sometimes|json|nullable',
            'sort_order' => 'sometimes'
        ]);

        $plan->fill($request->only(['name', 'description', 'price', 'currency', 'duration', 'sort_order', 'metadata']));

        $plan->save();

        return response()->json($plan);
    }

    public function destroy(PlanModel $plan, Request $request)
    {
        Gate::authorize('delete');

        $plan->delete();

        return response()->json([], 200);
    }
}
