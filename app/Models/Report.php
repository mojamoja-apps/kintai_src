<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $dates = [
        'day',
    ];

    //belongsTo設定
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }
    public function site()
    {
        return $this->belongsTo('App\Models\Site');
    }
    // hasMany設定
    public function reportworkings()
    {
        return $this->hasMany('App\Models\ReportWorking');
    }
    public function reportdrivers()
    {
        return $this->hasMany('App\Models\ReportDriver');
    }
}
