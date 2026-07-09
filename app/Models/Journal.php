<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $table = 'jrn_journal';
    protected $primaryKey = 'jrn_id';

    public $timestamps = false;

    protected $fillable = [
        'jrn_usr_id',
        'jrn_date',
        'jrn_prompt',
        'jrn_answer_text',
        'jrn_mood_ref',
        'jrn_created_at',
    ];

    public function user()
    {
        return $this->belongsTo(RiseUpUser::class, 'jrn_usr_id', 'usr_id');
    }
}
