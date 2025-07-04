<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'permiso_id';
    public $timestamps = false;

    protected $fillable = [
        'permiso_id',
        'permiso_nombre',
    ];
}
