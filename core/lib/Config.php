<?php
namespace core\lib;

class Config implements \ArrayAccess
{
    protected $path;
    protected $configs = array();//加载过的类

    function __construct($path)
    {
        $this->path = $path;
    }

    function offsetGet($key)//设置配置项
    {
        if (empty($this->configs[$key]))//如果没加载过
        {
            $file_path = $this->path.'/'.$key.'.php';
            $config = require $file_path;
            $this->configs[$key] = $config;
        }
        return $this->configs[$key];
    }

    function offsetSet($key, $value)
    {
        throw new \Exception("cannot write config file.");
    }

    function offsetExists($key)
    {
        return isset($this->configs[$key]);
    }

    function offsetUnset($key)
    {
        unset($this->configs[$key]);
    }
}