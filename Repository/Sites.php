<?php

namespace App\Repository;

class Sites extends AbstractRepository
{
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT id, trim(trim(BOTH '\t' FROM domain)) as domain FROM `sites` ORDER BY trim(trim(BOTH '\t' FROM domain))");
        
        return $this->fetchAll($stmt);
    }
}
