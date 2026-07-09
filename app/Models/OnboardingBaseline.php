<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingBaseline extends Model
{
    protected $table = 'bas_onboarding_baseline';
    protected $primaryKey = 'bas_id';

    public $timestamps = false;

    protected $fillable = [
        'bas_usr_id',
        'bas_exposure_duration',
        'bas_main_reason',
        'bas_target_goal',
        'bas_daily_duration',
        'bas_est_loss_monthly',
        'bas_est_income_monthly',
        'bas_risk_hour_start',
        'bas_risk_hour_end',
        'bas_created_at',
    ];

    public function user()
    {
        return $this->belongsTo(RiseUpUser::class, 'bas_usr_id', 'usr_id');
    }
}