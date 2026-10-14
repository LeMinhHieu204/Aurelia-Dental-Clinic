<?php

namespace App\Providers;

use App\Models\AppointmentRequest;
use App\Models\ContentSection;
use App\Models\MediaAsset;
use App\Models\Patient;
use App\Policies\AppointmentRequestPolicy;
use App\Policies\ContentSectionPolicy;
use App\Policies\MediaAssetPolicy;
use App\Policies\PatientPolicy;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(AppointmentRequest::class, AppointmentRequestPolicy::class);
        Gate::policy(ContentSection::class, ContentSectionPolicy::class);
        Gate::policy(MediaAsset::class, MediaAssetPolicy::class);
        Gate::policy(Patient::class, PatientPolicy::class);

        View::composer('layouts.app', function (ViewContract $view): void {
            $view->with('siteSettings', SiteSetting::where('is_public', true)->pluck('value', 'key'));
        });
    }
}
