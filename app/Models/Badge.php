<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    protected $table = 'bdg_badge';
    protected $primaryKey = 'bdg_id';

    public $timestamps = false;

    protected $fillable = [
        'bdg_code',
        'bdg_name',
        'bdg_description',
        'bdg_icon',
        'bdg_condition_type',
        'bdg_condition_value',
        'bdg_is_active',
        'bdg_created_at',
    ];
}
