<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReligiousContent extends Model
{
    protected $table = 'rel_religious_content';
    protected $primaryKey = 'rel_id';

    public $timestamps = false;

    protected $fillable = [
        'rel_religion_pref',
        'rel_category',
        'rel_text',
        'rel_source',
        'rel_is_active',
        'rel_created_at',
    ];
}
