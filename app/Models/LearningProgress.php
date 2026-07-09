<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LearningProgress extends Model
{
    protected $table = 'lrp_learning_progress';
    protected $primaryKey = 'lrp_id';

    public $timestamps = false;

    protected $fillable = [
        'lrp_usr_id',
        'lrp_lrn_id',
        'lrp_status',
        'lrp_completed_at',
    ];

    public function module()
    {
        return $this->belongsTo(MicrolearningModule::class, 'lrp_lrn_id', 'lrn_id');
    }

    public function user()
    {
        return $this->belongsTo(RiseUpUser::class, 'lrp_usr_id', 'usr_id');
    }
}