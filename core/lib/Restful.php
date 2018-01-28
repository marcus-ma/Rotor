<?php
namespace core\lib;
class Restful
{
    protected $getRoutes = [];
    protected $postRoutes = [];
    protected $putRoutes = [];
    protected $deleteRoutes = [];

    /**
     * 请求方法
     */
    private $_requestMethod;
    /**
     * 资源名称
     */
    private $_resourceName;
    /**
     * 请求ID
     */
    private $_id;
    /**
     * 允许请求的方法
     *
     * @var        array
     */
    private $_allowRequestMethods = ['GET','POST','PUT','DELETE'];
    /**
     * http请求状态码
     *
     * @var        array
     */
    private $_statusCodes = [
        200 => '请求成功',
        201 => '创建成功',
        202 => '更新成功',
        400 => '无效请求',
        401 => '地址不存在',
        403 => '禁止访问',
        404 => '请求资源不存在',
        405 => '请求方法不允许',
        500 => '服务端内部错误'
    ];

    private static $_instance = null;//该类中的唯一一个实例
    private function __construct(){}//防止在外部实例化该类
    private function __clone(){}//禁止通过复制的方式实例化该类
    public static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function get($routePath, $routeCallback)
    {
        $this->getRoutes[$routePath] = $routeCallback->bindTo($this, __CLASS__);
    }

    public function post($routePath, $routeCallback)
    {
        $this->postRoutes[$routePath] = $routeCallback->bindTo($this, __CLASS__);
    }

    public function put($routePath, $routeCallback)
    {
        $this->putRoutes[$routePath] = $routeCallback->bindTo($this, __CLASS__);
    }

    public function delete($routePath, $routeCallback)
    {
        $this->deleteRoutes[$routePath] = $routeCallback->bindTo($this, __CLASS__);
    }


    public function run()
    {
        try{
            $this->_setupRequestMethod();
            $this->_setupResource();
            $arr=$this->select($this->_requestMethod);
            if(isset($arr[$this->_resourceName])){
                $arr[$this->_resourceName]($this->_id);
            }else{
                throw new \Exception('请求资源不存在', 404);
            }
//            foreach ($this->select($this->_requestMethod) as $routePath => $callback)
//            {
//                if ($routePath === $this->_resourceName)
//                {
//                    $callback($this->_id);
//                }
//            }
        }catch(\Exception $e){
            $this->_json(['info'=>$e->getMessage()],$e->getCode());
        }

    }


    /**
     * 初始化请求方法
     */
    private function _setupRequestMethod(){
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        if(!in_array($this->_requestMethod, $this->_allowRequestMethods)){
            throw new \Exception("请求方法不被允许", 405);
        }
    }
    /**
     * 初始化请求资源
     */
    private function _setupResource(){
        $path = $_SERVER['PATH_INFO'];
        $params = explode('/',$path);
        $this->_resourceName = $params[1];
        if(!empty($params[2])){
            $this->_id = $params[2];
        }
    }

    private function select($requestMethod){
        switch ($requestMethod){
            case 'POST':
                $route = $this->postRoutes;
                break;
            case 'PUT':
                $route = $this->putRoutes;
                break;
            case 'DELETE':
                $route = $this->deleteRoutes;
                break;
            case 'GET':
                $route = $this->getRoutes;
                break;
            default:
                throw new \Exception("请求方法不被允许", 405);
        }
        return $route;
    }

    /**
     * 输出json
     *
     * @param      array  $array  The array
     */
    private function _json($array,$code=0){
        if($array === null && $code === 0){
            $code = 204;
        }
        if($array !== null && $code === 0){
            $code = 200;
        }
        header("HTTP/1.1 ".$code."  ".$this->_statusCodes[$code]);
        header("Content-Type=application/json;charset=UTF-8 ");
        if($array !== null){
            echo  json_encode($array,JSON_UNESCAPED_UNICODE);
        }
        exit();
    }
}
