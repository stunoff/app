<?php

namespace App\Repository;

class ApiCredentials extends AbstractRepository
{
    public function getCredentialsByName($serviceName)
    {
        $stmt = $this->db->prepare("SELECT * FROM api_credentials WHERE name = :service_name");
        $stmt->bindParam(':service_name', $serviceName);
        
        return $this->fetchOne($stmt);
    }
}
