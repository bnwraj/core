<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vtlabs\Core\Models\MobileLanguage;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Filters\MobileLanguageFilter;
use Vtlabs\Core\Http\Resources\MobileLanguageResource;

/**
 * @group  Mobile Languages
 *
 * APIs for mobile languages management
 */
class MobileLanguageController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'langauge_code' => 'sometimes',
            'type' => 'sometimes',
            'skip_content' => 'sometimes|boolean'
        ]);

        $mobileLanguages = MobileLanguage::filter($request->all(), MobileLanguageFilter::class);

        return MobileLanguageResource::collection($mobileLanguages->get());
    }
}
