<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveUpWithdrawal extends Model
{
    protected $table = 'wdr_saveup_withdrawal';
    protected $primaryKey = 'wdr_id';

    public $timestamps = false;

    protected $fillable = [
        'wdr_usr_id',
        'wdr_amount',
        'wdr_reason',
        'wdr_status',
        'wdr_admin_note',
        'wdr_processed_at',
        'wdr_created_at',
    ];

    public function user()
    {
        return $this->belongsTo(RiseUpUser::class, 'wdr_usr_id', 'usr_id');
    }
}
