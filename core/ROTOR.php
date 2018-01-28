<?php
namespace core;
use core\lib\Config;

class ROTOR
{
    private static $_instance = null;//该类中的唯一一个实例
    private $config;
    private function __construct(){//防止在外部实例化该类
        $this->config = new Config(ROTOR.'/app/config');
    }
    private function __clone(){}//禁止通过复制的方式实例化该类

    public static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 装饰方法（装饰器模式+观察者模式）
     * @param bool $model
     */
    public function Decorator($model = false, $return_value = null){
        //观察者列表
        $decorators = array();
        if (isset($this->config['DCRconf']['decorator']))
        {
            $conf_decorator = $this->config['DCRconf']['decorator'];
            foreach($conf_decorator as $class)
            {
                $decorators[] = new $class;
            }
        }
        if (($model==true)){
            foreach($decorators as $decorator)
            {
                $decorator->beforeRequest();
            }
        }else{
            foreach($decorators as $decorator)
            {
                $decorator->afterRequest($return_value);
            }
        }



    }
//    public function Decorator($obj, $func = NULL){
//        if(!is_callable([$obj,$func])){
//            die("该类下的{$func}方法不存在");
//        }
//        $decorators = array();
//        if (isset($this->config['DCRconf']['decorator']))
//        {
//            $conf_decorator = $this->config['DCRconf']['decorator'];
//            foreach($conf_decorator as $class)
//            {
//                $decorators[] = new $class;
//            }
//        }
//
//        foreach($decorators as $decorator)
//        {
//            $decorator->beforeRequest($obj);
//        }
//        //如果没返回值就不执行
//        if (!$obj->$func()){
//            exit;
//        }else{
//            $return_value=$obj->$func();
//        }
//        foreach($decorators as $decorator)
//        {
//            $decorator->afterRequest($return_value);
//        }
//
//    }
}