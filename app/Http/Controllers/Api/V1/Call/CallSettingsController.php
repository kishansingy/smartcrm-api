<?php

namespace App\Http\Controllers\Api\V1\Call;

use App\Http\Controllers\Controller;
use App\Support\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CallSettingsController extends Controller
{
    private string $settingsPath;

    public function __construct()
    {
        $this->settingsPath = storage_path('app/call_settings.json');
    }

    /**
     * Get current call provider settings.
     */
    public function show(): JsonResponse
    {
        $this->authorize('calls.view');

        return ApiResponse::success($this->loadSettings());
    }

    /**
     * Update call provider settings.
     */
    public function update(Request $request): JsonResponse
    {
        $this->authorize('calls.make');

        $validated = $request->validate([
            'default_provider' => ['required', 'string', 'in:retell,exotel'],
        ]);

        $settings = array_merge($this->loadSettings(), $validated);
        file_put_contents($this->settingsPath, json_encode($settings, JSON_PRETTY_PRINT));

        return ApiResponse::success($settings, 'Call settings updated.');
    }

    private function loadSettings(): array
    {
        if (file_exists($this->settingsPath)) {
            $data = json_decode(file_get_contents($this->settingsPath), true);
            if (is_array($data)) {
                return $data;
            }
        }

        return [
            'default_provider' => config('services.calls.default_provider', 'retell'),
        ];
    }
}
