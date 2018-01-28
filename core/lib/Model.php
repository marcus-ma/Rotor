<?php
namespace core\lib;
use app\config\DBware;
class Model{
    static $db;
    static $tableName;
    function __construct()
    {
        //获取继承的模型的名称作为表名 get_called_class()获取继承类的全称，此处获取为：app\Model\xxx
        $arr=explode('\\',get_called_class());
        self::$tableName = lcfirst($arr[2]);
        self::$db = DBware::connDb();
    }

    //增
    public static function create($arr = [])
    {
        return DBware::connDb()->form(lcfirst(explode('\\',get_called_class())[2]))->insert($arr);
    }

    //删
    public static function delete($whereSql= '1=1')
    {
        return DBware::connDb()->form(lcfirst(explode('\\',get_called_class())[2]))->where($whereSql)->delete();
    }

    //改
    public static function update($whereSql= '1=1',$arr = [])
    {
        return DBware::connDb()->form(lcfirst(explode('\\',get_called_class())[2]))->where($whereSql)->save($arr);
    }
    //查
    public static function fetchAll($whereSql= '1=1',$colSql = "*")
    {
        return DBware::connDb()->form(lcfirst(explode('\\',get_called_class())[2]))->where($whereSql)->select($colSql);
    }

    public static function fetchOne($whereSql= '1=1',$colSql = "*")
    {
         $row = DBware::connDb()->form(lcfirst(explode('\\',get_called_class())[2]))->where($whereSql)->limit(1,true)->select($colSql);
         return json_decode(json_encode($row));
    }
    
}