<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MicrolearningModule extends Model
{
    protected $table = 'lrn_microlearning_module';
    protected $primaryKey = 'lrn_id';

    public $timestamps = false;

    protected $fillable = [
        'lrn_day_number',
        'lrn_title',
        'lrn_category',
        'lrn_content',
        'lrn_xp_reward',
        'lrn_is_active',
        'lrn_created_at',
    ];

    public function progresses()
    {
        return $this->hasMany(LearningProgress::class, 'lrp_lrn_id', 'lrn_id');
    }
}