# Rotor V3使用手册

<br>
<br>

## #0简介

- rotor--一个为中小型SPA应用提供Restful API的MSR型PHP微框架。(MSR M:模型层；S:业务逻辑层；R:路由分发层)<br><br>

- rotor并不算是真正的框架，意义上来说用“PHP项目脚手架”来描述会更合适。<br><br>

- rotor适合那些有原生PHP基础，却对TP，YII，laravel等MVC框架开发模式不熟悉的开发者。它友好地提供一个文件规范、代码逻辑易维护、类MVC模式的目录架构，使初级开发者在做项目时可以抛弃页面功能逻辑与页面布局样式耦合的开发模式，减少开发和维护成本，且熟悉这种开发模式之后，可以迅速上手MVC框架。


<br>
<br>


## #1起步

<br>
<br>
rotor根目录下有4个文件目录和2个文件，分别为app，core，libs和static四个目录文件与数据交互层api.php,前端index.html两个文件

<br>


## #2入门

### 1.前后端分离

前端只一个html文件，通过ajax访问api.php来跟后端进行数据交互，业务逻辑类文件皆放在app目录下，统一采用OOP编程模式来封装业务逻辑代码，数据交互统一采用json格式。

<br>
### 2.目录文件


`**#rotor**  
   根目录文件  
  
  **#app**  
  
      放置业务逻辑类文件。
      app
      |-config(配置文件)
      |---DBconf.php(数据库配置文件)
      |---DBware.php(数据库操作的中间件类)
      |-Model(模型文件)
      |---Posts.php(Posts表模型)
      |-Service(业务文件)
      |---Index.php(Index业务)
      |-Route.php(路由文件)
  
  **#core**  
      框架的核心文件，不可改动，  
  
  **#libs**  
      放置第三方类库和自定义类文件  
  
  **#static**  
      放置静态资源文件（css、js、image）  
  
  **#api.php**  
      后端的入口文件，框架的启动文件，前端发起的资源请求都经过此处进行路由转发执行相对应的业务逻辑  
 
  
  **#index.html**  
      前端文件  

<br>

### 3.数据库配置
数据库配置文件为app目录下的config文件夹里的DBconf.php，按照里面的格式填写参数即可。 
``` php  
    <?php  
    return array(  
        //表名  
        'database_name' => '',  
        //数据库地址  
        'server' => '',  
        //数据库用户名  
        'username' => '',  
        //数据库密码  
        'password' => '',  
        //字符串类型  
        'charset' => 'utf8',  
    );  
  
```  
</br>

### 4.路由自定义
最基本的 路由只接收一个 URI 和一个闭包，并以此提供一个非常简单且优雅的定义路由方法：
``` php  
    <?php  
    $route->get('rotor',function(){
      echo "get i am rotor";
   });
  
```  
有时我们需要在路由中捕获 URI 片段。比如，要从 URL 中捕获用户ID，需要通过如下方式定义路由参数：
``` php  
    <?php  
      $route->post('login',function($id = 123){
         echo "the user id  is {$id}";
      });
  
```  
对于指定的路由执行相对的业务逻辑方法如下：
``` php  
    <?php  
    //当接收到post请求hello资源时，执行Service下的Index业务类的hello方法
      $route->post('hello',function(){
         \app\Service\Index::getInstance()->hello();
      });
```  
请求的路由方式主要为4种：get、post、delete、put，这对应了Restful风格的4种请求状态，详细可以查询相关资料进行了解。
</br>

### 5.业务类
最基本的业务类格式如下：（Index模块类为例子）
``` php  
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
    
    //自定义一个hello方法
    public function hello(){
        if ($_POST['key'] == 'hello'){
            echo '<style type="text/css">*{ padding: 0; margin: 0; } .think_default_text{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"><br><br><h1>Rotor</h1><br><br><p>为专注于前后端分离而生——<br/><span style="font-size:30px">一个为中小型SPA应用提供Restful API的MSR型PHP微框架</span></p></div>';
        }else{
           _json(400,'无效请求',['name' => 'Rotor']);
        }
    }

}
  
```  

### 6.数据库操作
数据库操作支持链式结构和原生sql两种。先调用DBware::connDB()赋给变量:$db=DBware::connDB()，  
**#链式结构操作**  
      ##增  
``` php  
    <?php  
    $db=DBware::connDB(); 
    $a = [  
                    "username" => 'nero',  
                    "click_num" => 45  
             ];  
    $db->form('表名')->insert($a); 
  
```  
      
  ###删  
  ``` php  
    <?php  
    $db=DBware::connDB(); 
    $db->form('表名')->where('username="der"')->delete(); 
```  
###改  
  ``` php  
    <?php 
    $db=DBware::connDB(); 
    $a = [  
                    'username' => 'marcus',  
                    "click_num" => 45  
               ]; 
    $db->form('表名')->where('id=1')->save($a);  
```  
  ###查  
  ``` php  
    <?php 
    $db=DBware::connDB(); 
    //返回对象集  
    $a=$db->form("表名")->where('id=1')->select();  
    echo $a->click_num;
```  

  
**#原生sql操作操作**  
  ``` php  
    <?php  
    $db=DBware::connDB();  
    $db->query("SELECT * FROM 表名 WHERE 字段=值");  
```  
   
  
调用前得先引入app目录下的config文件夹里的DBware类文件(use config/DBware)。  
