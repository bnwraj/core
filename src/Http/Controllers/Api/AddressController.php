<?php

namespace Vtlabs\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vtlabs\Core\Models\Address;
use Illuminate\Support\Facades\Auth;
use Vtlabs\Core\Http\Controllers\Controller;
use Vtlabs\Core\Http\Resources\AddressResource;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', Auth::user()->id)->get();
        return AddressResource::collection($addresses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'formatted_address' => 'required',
            'address1' => 'sometimes',
            'address2' => 'sometimes',
            'formatted_address' => 'required',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'meta' => 'sometimes|json'
        ]);

        $address = Address::create([
            'title' => $request->title,
            'formatted_address' => $request->formatted_address,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'longitude' => $request->longitude,
            'latitude' => $request->latitude,
            'user_id' => Auth::user()->id,
            'meta' => isset($request->meta) ? json_decode($request->meta) : null
        ]);

        return new AddressResource($address);
    }

    public function update(Address $address, Request $request)
    {
        $request->validate([
            'title' => 'required',
            'formatted_address' => 'required',
            'address1' => 'sometimes',
            'address2' => 'sometimes',
            'longitude' => 'required|numeric',
            'latitude' => 'required|numeric',
            'meta' => 'sometimes|json'
        ]);

        if ($request->meta) {
            request()->merge([
                "meta" => json_decode($request->meta)
            ]);
        }

        $address->fill($request->only(['title', 'formatted_address', 'longitude', 'latitude', 'address1', 'address2', 'meta']));

        $address->save();

        return new AddressResource($address);
    }

    public function destroy(Address $address, Request $request)
    {
        $address->delete();
        return response()->json((object)[]);
    }
}