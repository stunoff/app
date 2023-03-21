<?php

namespace App\Repository;

class Statuses extends AbstractRepository
{
    public function getAll()
    {
        $stmt = $this->db->prepare('SELECT * FROM new_status');
        
        return $this->fetchAll($stmt);
    }

    public function getOperStatuses()
    {
        $stmt = $this->db->prepare('SELECT * FROM new_status WHERE tip = 0 ORDER BY name');

        return $this->fetchAll($stmt);
    }

    public function getLogistStatuses()
    {
        $stmt = $this->db->prepare('SELECT * FROM new_status WHERE tip = 1 ORDER BY name');

        return $this->fetchAll($stmt);
    }
}
