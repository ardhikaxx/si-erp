<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        try {
            $settings = Cache::remember('app_settings', 3600, function () {
                return Setting::pluck('value', 'key')->toArray();
            });

            if (!empty($settings)) {
                Config::set('settings', $settings);
            }

            if (isset($settings['app_name'])) {
                Config::set('app.name', $settings['app_name']);
            }

            if (isset($settings['timezone'])) {
                Config::set('app.timezone', $settings['timezone']);
            }

            View::share('companySettings', $settings);
        } catch (\Exception $e) {
            // skip if table not exists
        }
    }
}
