<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeService
{

    /*
        * クライアントIDで絞った有効社員一覧
    */
    public function findEmployeesByClientId($client_id)
    {
        $query = Employee::query();

        if ($client_id) {
            $query->where('client_id', $client_id);
        }
        $query->where('is_enabled', true);
        return $query->orderBy('order', 'asc')->orderBy('updated_at', 'desc')->orderBy('id', 'desc')->get();
    }
}
