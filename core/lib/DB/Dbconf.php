<?php
namespace core\lib\DB;
class Dbconf
{
    //建立一个缓存数组变量存储加载的配置项
    static private $conf=[];


    //加载配置的方法，第一个参数为配置文件名称,此为加载整个配置文件；第二个参数为某个配置项的名称(可选)，此为加载某个配置文件中的单个配置项。
    static public function get($file,$name=null)
    {
        //如果加载过该配置文件，则直接返回
        if (isset(self::$conf[$file])){
            return self::$conf[$file];
        }else{
            $path=ROTOR.'/app/config/'.$file.'.php';//配置文件的名称
            //判断该配置文件是否存在
            if (is_file($path)){
                //把配置文件加载进来，同时放进缓存数组中
                self::$conf[$file]=include $path;
                $config=self::$conf[$file];
                //判断配置项是否为空，空则返回配置文件中的所有配置项
                if (is_null($name))
                    //返回配置对象
                    return json_decode(json_encode($config));
                else{//不为空则返回该单个配置项
                    return isset($config[$file][$name]) ? $config[$file][$name] : false;
                }

            }else
                throw new \Exception('找不到该配置文件'.$path);
        }
    }
}