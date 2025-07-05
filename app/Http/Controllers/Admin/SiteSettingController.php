<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteSettingController extends Controller
{
    protected $settingsService;

    public function __construct(SiteSettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function index()
    {
        $settings = SiteSetting::orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'groups' => SiteSetting::distinct('group')->pluck('group'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['required'],
        ]);

        $this->settingsService->updateMany($validated['settings']);

        return back()->with('success', 'Settings updated successfully.');
    }

    public function updateGroup(Request $request, string $group)
    {
        $settings = SiteSetting::where('group', $group)->get();
        
        $rules = [];
        foreach ($settings as $setting) {
            $key = "settings.{$setting->key}";
            $rules[$key] = $setting->validation_rules ?? ['nullable'];
        }

        $validated = $request->validate($rules);

        $this->settingsService->updateMany($validated['settings']);

        return back()->with('success', 'Settings updated successfully.');
    }
} 