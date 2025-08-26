<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // (public)
    public function viewTerms()
    {
        $setting = Setting::first();
        return response()->json([
            'success' => true,
            'terms_and_conditions' => $setting?->terms_and_conditions
        ]);
    }

    //(public)
    public function viewSettings()
    {
        $setting = Setting::first();
        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }

    //(admin)
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'logo_url' => 'nullable|string',
            'address' => 'nullable|string',
            'working_hours' => 'nullable|string',
            'about_image_url' => 'nullable|string',
            'about_description' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'facebook_url' => 'nullable|string',
            'whatsapp_number' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'second_phone_number' => 'nullable|string',
        ]);

        $setting = Setting::first();
        if (!$setting) {
            $setting = Setting::create($request->all());
        } else {
            $setting->update($request->all());
        }

        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }
}
