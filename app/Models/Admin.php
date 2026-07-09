<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'adm_admin';
    protected $primaryKey = 'adm_id';

    public $timestamps = false;

    protected $fillable = [
        'adm_email',
        'adm_password_hash',
        'adm_name',
    ];
}