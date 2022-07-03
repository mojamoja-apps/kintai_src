<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kintai extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $dates = [
        'day',
        'time_1',
        'time_2',
        'time_3',
        'time_4',
        'time_5',
        'time_6',
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
