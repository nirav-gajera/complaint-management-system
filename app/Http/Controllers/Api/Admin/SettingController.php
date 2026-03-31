<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingUpdateRequest;
use App\Http\Resources\SystemSettingResource;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => SystemSettingResource::collection(SystemSetting::query()->orderBy('group')->orderBy('label')->get()),
        ]);
    }

    public function update(SettingUpdateRequest $request): JsonResponse
    {
        foreach ($request->input('settings', []) as $settingId => $payload) {
            $setting = SystemSetting::query()->find($settingId);

            if ($setting) {
                $setting->update([
                    'value' => $payload['value'] ?? null,
                ]);
            }
        }

        return response()->json([
            'message' => 'Settings updated successfully.',
            'data' => SystemSettingResource::collection(SystemSetting::query()->orderBy('group')->orderBy('label')->get()),
        ]);
    }
}
