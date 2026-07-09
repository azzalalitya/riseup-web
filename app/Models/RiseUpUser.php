<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiseUpUser extends Model
{
    protected $table = 'usr_user';
    protected $primaryKey = 'usr_id';

    public $timestamps = false;

    protected $fillable = [
        'usr_email',
        'usr_password_hash',
        'usr_google_id',
        'usr_avatar_url',
        'usr_status',
    ];

    public function gamification()
    {
        return $this->hasOne(GamificationStat::class, 'gms_usr_id', 'usr_id');
    }

    public function checkins()
    {
        return $this->hasMany(DailyCheckin::class, 'chk_usr_id', 'usr_id');
    }

    public function onboarding()
    {
        return $this->hasOne(OnboardingBaseline::class, 'bas_usr_id', 'usr_id');
    }
    public function saveupTarget()
    {
        return $this->hasOne(SaveUpTarget::class, 'sav_usr_id', 'usr_id');
    }

    public function profile()
    {
        return $this->hasOne(\App\Models\UserProfile::class, 'prf_usr_id', 'usr_id');
    }
}