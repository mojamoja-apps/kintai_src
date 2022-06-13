<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    //belongsTo設定
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }
}
