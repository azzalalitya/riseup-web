<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $table = 'prf_user_profile';
    protected $primaryKey = 'prf_id';

    public $timestamps = false;

    protected $fillable = [
        'prf_usr_id',
        'prf_full_name',
        'prf_age_years',
        'prf_religion_pref',
        'prf_updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(RiseupUser::class, 'prf_usr_id', 'usr_id');
    }
}