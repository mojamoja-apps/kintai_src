<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kintai_rireki extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $dates = [
        'day',
    ];

    //belongsTo設定
    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee');
    }
}
