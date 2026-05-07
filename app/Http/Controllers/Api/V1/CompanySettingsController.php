<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySetting\UpdateCompanySettingsRequest;
use App\Models\CompanySetting;
use App\Support\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Single-tenant company settings (key/value).
 *
 * Read endpoint is open to any authenticated user (e.g. for showing the company
 * name in a header). Write endpoint requires manage_company_settings.
 */
class CompanySettingsController extends Controller
{
    use ApiResponse;

    public function show(Request $_request): JsonResponse
    {
        return $this->success([
            'name' => CompanySetting::get('name'),
        ]);
    }

    public function update(UpdateCompanySettingsRequest $request): JsonResponse
    {
        foreach ($request->validated() as $key => $value) {
            CompanySetting::set($key, $value);
        }

        return $this->success([
            'name' => CompanySetting::get('name'),
        ], 'تم حفظ إعدادات الشركة.');
    }
}
