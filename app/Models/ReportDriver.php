<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDriver extends Model
{
    use HasFactory;

    // プライマリキー設定
    protected $primaryKey = ['report_id', 'no'];
    // increment無効化
    public $incrementing = false;

    protected $fillable = [
        'report_id',
        'no',
        'worker_id',
    ];

    //belongsTo設定
    public function report()
    {
        return $this->belongsTo('App\Models\Report');
    }
    public function worker()
    {
        return $this->belongsTo('App\Models\Worker');
    }
}
