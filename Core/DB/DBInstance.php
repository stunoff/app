<?php

namespace App\Core\DB;

use App\Utils\Config;
use PDO;
use PDOException;

class DBInstance
{
    private static $instance = null;
    /**
     * @var \PDO
     */
    private $conn;
    
    private function __construct()
    {
        $dbConfig = Config::getDBConfig();
        $host     = $dbConfig['db_host'];
        $DBName   = $dbConfig['db_name'];
        $userName = $dbConfig['db_login'];
        $password = $dbConfig['db_pass'];
        
        try {
            $this->conn = new PDO(
                "mysql:host=$host;dbname=$DBName",
                $userName,
                $password,
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage());
        }
    }
    
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DBInstance();
        }
    
        /** @var \PDO $instance*/
        return self::$instance;
    }
    
    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->conn;
    }
}
