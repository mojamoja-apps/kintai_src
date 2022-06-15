<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Client extends Authenticatable
{
    use HasFactory;

    protected $guarded = ['id'];

    //hasMany設定
    public function sites()
    {
        return $this->hasMany('App\Models\Site');
    }
}
