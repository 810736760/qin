<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rights extends Model
{
    protected $table = 'rights';
    protected $guarded = ['id'];
    // protected $fillable = [
    //     'name', 'path', 'from'
    // ];
}
