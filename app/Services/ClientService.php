<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{

    /*
        * ハッシュを元に有効クライアント1つを検索
    */
    public function findClientByHash($hash)
    {
        $query = Client::query();

        if ($hash) {
            $query->where('hash', $hash);
        }
        $query->where('is_enabled', true);
        return $query->first();
    }
}
