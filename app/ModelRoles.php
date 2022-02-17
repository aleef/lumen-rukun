<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModelRoles extends Model
{

    protected $table = 'model_has_roles';
    public $timestamps = false;
    protected $primaryKey = 'model_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'model_type', 'model_id',
    ];



}
