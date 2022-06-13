<?php

namespace App\Services;

use App\Models\Report;

class DeduraService
{

    /*
        * 元請けID、作業所ID、日指定で作業証明書データを取得
    */
    public function getDeduraByDay($company_id, $site_id, $day)
    {
        $query = Report::query();

        if ($company_id) {
            $query->where('company_id', $company_id);
        }
        if ($site_id) {
            $query->where('company_id', $site_id);
        }
        if ($site_id) {
            $query->where('day', $day);
        }

        return $query->orderBy('updated_at', 'desc')->first();
    }
}
