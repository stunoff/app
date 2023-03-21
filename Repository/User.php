<?php

namespace App\Repository;

class User extends AbstractRepository
{
    public function getUser($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM new_user WHERE id = :user_id");
        $stmt->bindParam(':user_id', $userId);
        
        return $this->fetchOne($stmt);
    }
}
