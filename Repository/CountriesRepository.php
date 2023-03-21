<?php

namespace App\Repository;

class CountriesRepository extends AbstractRepository
{
    public function getAll()
    {
        $stmt = $this->db->prepare("SELECT * FROM countries");
        
        return $this->fetchAll($stmt);
    }
}
