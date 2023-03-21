<?php

namespace App\Repository;

class Navigation extends AbstractRepository
{
    public function getNavigation()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM navigation
            WHERE show_in_menu IS TRUE
            ORDER BY head_menu ='Меню' DESC, page_title
        ");
        
        return $this->fetchAll($stmt);
    }
    
    public function getRoutes()
    {
        $stmt = $this->db->prepare("
            SELECT page_url, controller, method FROM navigation WHERE controller IS NOT NULL
        ");
        
        return $this->fetchAll($stmt);
    }
    
    public function getPermissions($url)
    {
        $stmt = $this->db->prepare('
            SELECT groups_access, single_user_access FROM navigation WHERE page_url = :url
        ');
        $stmt->bindValue(':url', $url);
        
        return $this->fetchOne($stmt);
    }
}
