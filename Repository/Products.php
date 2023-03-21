<?php

namespace App\Repository;

class Products extends AbstractRepository
{
    public function getProductList()
    {
        $stmt = $this->db->query('SELECT id, name FROM new_product');

        return $this->fetchKeyPair($stmt);
    }
}
