<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{

    /*
        * ハッシュを元に有効クライアントを検索
    */
    public function findByHash($hash)
    {
        $query = Client::query();

        if ($hash) {
            $query->where('hash', $hash);
        }
        $query->where('is_enabled', true);
        return $query->first();
    }
}
