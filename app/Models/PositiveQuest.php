<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositiveQuest extends Model
{
    protected $table = 'qst_positive_quest';
    protected $primaryKey = 'qst_id';

    public $timestamps = false;

    protected $fillable = [
        'qst_title',
        'qst_category',
        'qst_description',
        'qst_duration_min',
        'qst_xp_reward',
        'qst_is_active',
        'qst_created_at',
    ];

    public function progresses()
    {
        return $this->hasMany(QuestProgress::class, 'qrp_qst_id', 'qst_id');
    }
}