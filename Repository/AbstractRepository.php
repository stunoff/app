<?php

namespace App\Repository;

use App\Core\DB\DBInstance;
use Exception;
use PDO;
use PDOStatement;

abstract class AbstractRepository
{
    /**
     * @var PDO
     */
    protected $db = null;

    protected static $table;

    public function __construct()
    {
        $this->db = $this->getConnection();
    }

    protected function getConnection()
    {
        return DBInstance::getInstance()->getConnection();
    }
    
    protected function fetchAll(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function fetchColumn(PDOStatement $stmt)
    {
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    protected function fetchKeyPair(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    
    protected function fetchOne(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    protected function fetchField(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    protected function insert(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    protected function exec(PDOStatement $stmt)
    {
        return $stmt->execute();
    }

    public function one($id)
    {
        if (empty(static::$table)) {
            throw new Exception(get_class($this) . ': empty(static::$table) == true');
        }

        $table = static::$table;
        $stmt = $this->db->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->bindValue('id', $id);

        return $this->fetchOne($stmt);
    }

    public function all()
    {
        if (empty(static::$table)) {
            throw new Exception(get_class($this) . ': empty(static::$table) == true');
        }

        $table = static::$table;
        $stmt = $this->db->prepare("SELECT * FROM $table");

        return $this->fetchAll($stmt);
    }
}
