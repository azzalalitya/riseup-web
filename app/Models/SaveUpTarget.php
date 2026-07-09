<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveUpTarget extends Model
{
    protected $table = 'sav_saveup_target';
    protected $primaryKey = 'sav_id';

    public $timestamps = false;

    protected $fillable = [
        'sav_usr_id',
        'sav_target_name',
        'sav_target_amount',
        'sav_created_at',
    ];

    public function user()
    {
        return $this->belongsTo(RiseUpUser::class, 'sav_usr_id', 'usr_id');
    }
}