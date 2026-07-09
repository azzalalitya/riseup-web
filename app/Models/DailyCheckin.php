<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyCheckin extends Model
{
    protected $table = 'chk_daily_checkin';
    protected $primaryKey = 'chk_id';

    public $timestamps = false;

    protected $fillable = [
        'chk_usr_id',
        'chk_date',
        'chk_mood',
        'chk_urge_level',
        'chk_trigger',
        'chk_status_color',
        'chk_relapse_reason',
        'chk_note_text',
        'chk_created_at',
    ];

    public function user()
    {
        return $this->belongsTo(RiseupUser::class, 'chk_usr_id', 'usr_id');
    }
}