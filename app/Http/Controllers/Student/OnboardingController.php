<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\OnboardingBaseline;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function create()
    {
        $existing = OnboardingBaseline::where('bas_usr_id', session('auth_id'))->first();

        if ($existing) {
            return redirect()->route('student.dashboard');
        }

        return view('student.onboarding');
    }

    public function store(Request $request)
    {
       $request->validate([
    'exposure_duration' => 'required|in:<6m,6-12m,>12m',
    'main_reason' => 'required|in:stres,bosan,teman,uang,lainnya',
    'target_goal' => 'required|in:stop,reduce_frequency,reduce_duration',
    'daily_duration' => 'required|in:<30m,30-60m,1-2h,>2h',
    'estimated_loss' => 'nullable|numeric|min:0',
    'monthly_income' => 'nullable|numeric|min:0',
    'risk_hour_start' => 'nullable|date_format:H:i',
    'risk_hour_end' => 'nullable|date_format:H:i',
]);

        OnboardingBaseline::updateOrCreate(
            [
                'bas_usr_id' => session('auth_id'),
            ],
            [
                'bas_exposure_duration' => $request->exposure_duration,
                'bas_main_reason' => $request->main_reason,
                'bas_target_goal' => $request->target_goal,
                'bas_daily_duration' => $request->daily_duration,
                'bas_est_loss_monthly' => $request->estimated_loss,
                'bas_est_income_monthly' => $request->monthly_income,
                'bas_risk_hour_start' => $request->risk_hour_start,
                'bas_risk_hour_end' => $request->risk_hour_end,
                'bas_created_at' => now(),
            ]
        );

        return redirect()
            ->route('student.dashboard')
            ->with('success', 'Onboarding berhasil disimpan. Selamat mulai perjalanan RiseUp!');
    }
}