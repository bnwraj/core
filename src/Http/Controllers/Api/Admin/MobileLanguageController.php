<?php

namespace Vtlabs\Core\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Vtlabs\Core\Models\MobileLanguage;
use Vtlabs\Core\Filters\MobileLanguageFilter;
use Vtlabs\Core\Http\Controllers\Controller;    
use Vtlabs\Core\Http\Resources\Admin\MobileLanguageAdminResource;

class MobileLanguageController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'skip_content' => 'sometimes|boolean',
            'language_code' => 'sometimes'
        ]);

        $mobileLanguages = MobileLanguage::filter($request->all(), MobileLanguageFilter::class);

        return MobileLanguageAdminResource::collection($mobileLanguages->paginate());
    }

    public function store(Request $request)
    {
        $request->validate([
            'language_code' => 'required|max:6',
            'language_title' => 'required',
            'content' => 'required|json',
            'type' => 'required'
        ]);

        request()->merge([
            "content" => json_decode($request->content)
        ]);

        $mobileLanguage = MobileLanguage::create($request->only(['language_code', 'language_title', 'content', 'type']));

        $mobileLanguage->save();

        return new MobileLanguageAdminResource($mobileLanguage->fresh());
    }

    public function show(MobileLanguage $mobilelanguage)
    {
        return new MobileLanguageAdminResource($mobilelanguage);
    }

    public function update(MobileLanguage $mobilelanguage, Request $request)
    {
        $request->validate([
            'language_code' => 'required|max:6',
            'language_title' => 'required',
            'content' => 'required|json',
            'type' => 'required'
        ]);

        request()->merge([
            "content" => json_decode($request->content)
        ]);

        $mobilelanguage->fill($request->only(['language_code', 'language_title', 'content', 'type']));

        $mobilelanguage->save();

        return new MobileLanguageAdminResource($mobilelanguage->fresh());
    }

    public function destroy(MobileLanguage $mobilelanguage)
    {
        Gate::authorize('delete');

        $mobilelanguage->delete();

        return response()->json([], 200);
    }
}
