<?php

namespace App\Utils;

class Config
{
    static $config = array(
        'db' => array(
            'dev' => array(
                'db_host'  => DB_DEV_HOST,
                'db_name'  => DB_DEV_NAME,
                'db_login' => DB_DEV_LOGIN,
                'db_pass'  => DB_DEV_PASS,
            ),
            'prod'  => array(
                'db_host'  => DB_PROD_HOST,
                'db_name'  => DB_PROD_NAME,
                'db_login' => DB_PROD_LOGIN,
                'db_pass'  => DB_PROD_PASS,
            ),
        ),
    );
    
    public static function getDBConfig()
    {
        $domain = ! empty($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : 'cli';
        
        if (strpos($domain, '.test') !== false
            || strpos($domain, '.local') !== false
            || strpos($domain, '.dev') !== false
        ) {
            return self::$config['db']['dev'];
        }

        return self::$config['db']['prod'];
    }
}
