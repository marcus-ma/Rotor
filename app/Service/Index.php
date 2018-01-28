<?php
namespace app\Service;
class Index
{
    private static $_instance = null;//该类中的唯一一个实例
    private function __construct(){//防止在外部实例化该类
    }
    private function __clone(){}//禁止通过复制的方式实例化该类

    public static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function hello(){
        if ($_POST['key'] == 'hello'){
            echo '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"><br><br><h1>Rotor</h1><br><br><p>为专注于前后端分离而生——<br/><span style="font-size:30px">一个为中小型SPA应用提供Restful API的MSR型PHP微框架</span></p></div>';
        }else{
           _json(400,'无效请求',['name' => 'Rotor']);
        }
    }
    
    public function reqFalse(){
        echo "服务繁忙，请求失败";
    }
    

    
}
