<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    protected $table = 'ubd_user_badge';
    protected $primaryKey = 'ubd_id';

    public $timestamps = false;

    protected $fillable = [
        'ubd_usr_id',
        'ubd_bdg_id',
        'ubd_earned_at',
    ];

    public function badge()
    {
        return $this->belongsTo(Badge::class, 'ubd_bdg_id', 'bdg_id');
    }
}
