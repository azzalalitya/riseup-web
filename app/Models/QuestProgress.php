<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestProgress extends Model
{
    protected $table = 'qrp_quest_progress';
    protected $primaryKey = 'qrp_id';

    public $timestamps = false;

    protected $fillable = [
        'qrp_usr_id',
        'qrp_qst_id',
        'qrp_date',
        'qrp_status',
        'qrp_completed_at',
    ];

    public function quest()
    {
        return $this->belongsTo(PositiveQuest::class, 'qrp_qst_id', 'qst_id');
    }

    public function user()
    {
        return $this->belongsTo(RiseUpUser::class, 'qrp_usr_id', 'usr_id');
    }
}