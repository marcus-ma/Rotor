<?php
namespace app\config;
use core\lib\DB\MYSQLI;

/**
 * Class DBware (工厂模式)
 * @package app\config
 */
class DBware
{
    static public function connDb()
    {
        $db = MYSQLI::getInstance();
        return $db;
    }
    
    
}