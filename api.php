<?php
header("Cache-control:no-cache");
define('ROTOR',realpath('./'));//根目录
define('DEBUG',true);
//是否开启调试模式
if (DEBUG){
    ini_set('display_error','On');
}else{
    error_reporting(0);
}
require_once "core/common/Function.php";//加载函数库
require_once "core/autoload.php";
use core\lib\Restful;
$route = Restful::getInstance();
require_once "app/Route.php";
$route->run();

