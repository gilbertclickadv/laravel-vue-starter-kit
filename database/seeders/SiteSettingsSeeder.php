<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultSettings = SiteSettingsService::getDefaultSettings();

        foreach ($defaultSettings as $setting) {
            SiteSetting::create($setting);
        }
    }
} 