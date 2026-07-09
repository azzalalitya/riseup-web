<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaveUpDeposit extends Model
{
    protected $table = 'sav_saveup_deposit';
    protected $primaryKey = 'dep_id';

    public $timestamps = false;

    protected $fillable = [
        'dep_usr_id',
        'dep_amount',
        'dep_note',
        'dep_date',
        'dep_status',
        'dep_source',
        'dep_order_id',
        'dep_payment_type',
        'dep_paid_at',
        'dep_created_at',
    ];

    public function user()
    {
        return $this->belongsTo(RiseUpUser::class, 'dep_usr_id', 'usr_id');
    }
}