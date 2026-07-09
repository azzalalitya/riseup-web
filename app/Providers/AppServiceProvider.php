<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\OnboardingBaseline;
use App\Models\UserProfile;

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
        // Share data Buddy (nama + jam rawan) ke partial maskot,
        // sehingga bisa di-@include di view student mana pun tanpa
        // mengubah controller.
        View::composer('student.partials.buddy', function ($view) {
            $userId = session('auth_id');

            $riskStart = null;
            $riskEnd = null;
            $name = 'Kamu';

            if ($userId) {
                $baseline = OnboardingBaseline::where('bas_usr_id', $userId)->first();

                if ($baseline) {
                    $riskStart = $baseline->bas_risk_hour_start;
                    $riskEnd = $baseline->bas_risk_hour_end;
                }

                $profile = UserProfile::where('prf_usr_id', $userId)->first();
                if ($profile && $profile->prf_full_name) {
                    $name = $profile->prf_full_name;
                }
            }

            // Default jam rawan (malam) bila user belum mengaturnya
            if (!$riskStart || !$riskEnd) {
                $riskStart = '20:00';
                $riskEnd = '23:00';
            }

            $view->with([
                'buddyName'      => $name,
                'buddyRiskStart' => substr((string) $riskStart, 0, 5),
                'buddyRiskEnd'   => substr((string) $riskEnd, 0, 5),
            ]);
        });
    }
}
