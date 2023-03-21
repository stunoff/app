<?php

namespace App\Repository;

class ActionPay extends AbstractRepository
{
    public function getByIds($ids)
    {
        $quotes = trim(str_repeat('?, ', count($ids)), ', ');
        
        $stmt = $this->db->prepare("SELECT id,name FROM new_dostavka WHERE id IN ($quotes)");
        $stmt->execute($ids);
        
        $ret = $this->fetchKeyPair($stmt);
        $ret[0] = 'Все';
        uasort($ret, function($a, $b) {
            return $a - $b;
        });
        
        return $ret;
    }
}
