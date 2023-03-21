<?php

namespace App\Repository;

class AgeRepo extends AbstractRepository
{
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM age_list");
        
        return $this->fetchAll($stmt);
    }
}
