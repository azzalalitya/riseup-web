<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationStat extends Model
{
    protected $table = 'gms_gamification_stat';
    protected $primaryKey = 'gms_id';

    public $timestamps = false;

    protected $fillable = [
        'gms_usr_id',
        'gms_total_xp',
        'gms_level_num',
        'gms_weekly_xp',
        'gms_updated_at',
    ];
}