<?php
namespace core\lib\DB;
class MYSQLI implements IDbInfo
{
    protected static $_instance = null;//该类中的唯一一个实例
    private $_conn;//数据库连接
    private $_sql = ["form" => '', "where" => '', "limit" => '', "order" => ''];//sql语句关键词
    private function __construct(){//防止在外部实例化该类
        $conf=Dbconf::get('DBconf');
        $conn = mysqli_connect($conf->server,$conf->username,$conf->password,$conf->database_name);
        if($conn->connect_error){
            die("连接失败,result is".mysqli_connect_error());
        }
        $this->_conn = $conn;
        mysqli_set_charset($this->_conn, $conf->charset);
        
    }
    private function __clone(){}//禁止通过复制的方式实例化该类

    //单例实例化
    static function getInstance(){
        if(self::$_instance == null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    //查询语句
    function query($sql)
    {
        if(!$sql && preg_match('/or/',$sql)){
            throw new \Exception("请求无效", 400);
        }
        $res=mysqli_query($this->_conn,$sql);
        return $res;
    }

    //表名
    function form($tableName){
        $this->_sql["form"] = "FROM ".$tableName;
        return $this;
    }

    //条件
    function where($_where = '1=1'){
        $_where=mysqli_real_escape_string($this->_conn,$_where);
        $this->_sql["where"] = "WHERE ".$_where;
        return $this;
    }

    //顺序
    function order($_order = 'id DESC'){
        $this->_sql["order"] = "ORDER BY ".$_order;
        return $this;
    }

    //限条
    function limit($_limit = 30,$getLine = false){
        if ($getLine){
            $this->_sql["limit"] = "LIMIT {$getLine}";
        }else{
            $this->_sql["limit"] = "LIMIT 0,".$_limit; 
        }
        return $this;
    }

    //选取
    function select($_select = '*'){
        $row = [];
        $sql = "SELECT ".$_select." ".(implode(" ",$this->_sql));
        $res = $this->query($sql);
        if (!$res){
            echo json_encode(['info'=>'unfound'],JSON_UNESCAPED_UNICODE);
            exit;
        }
        while ($line=$res->fetch_assoc()){
            $row[]=$line;
        }
        //$res=json_decode(json_encode($row));
        return $row;
    }

    //插入
    function insert($arr = []){
        //insert into formname (ziduan) value ('duiying')
        // implode(',',array_keys($arr));//前一个括号
        // implode(',',$arr);//后一个括号
        // substr($this->sql['form'],5);//表名
        if (!is_array($arr)){
            throw new \Exception("请传入数组参数", 500);
        }
        $this->_sql['form']=substr($this->_sql['form'],5);
        $sql = "INSERT INTO ".$this->_sql['form']." (".implode(',',array_keys($arr)).") VALUES ('".implode("','",$arr)."') ";
        return $this->query($sql);
    }

    //删除
    function delete(){
        //DELETE FROM Person WHERE LastName = 'Wilson'
        $sql = "DELETE ".(implode(" ",$this->_sql));
        return $this->query($sql);
    }

    //更改
    function save($arr = []){
        //UPDATE Person SET FirstName = 'Fred' WHERE LastName = 'Wilson'
        if (!is_array($arr)){
            die("请传入数组参数");
        }
        $a = [];
        foreach ($arr as $key=>$value){
            $a[] = $key." = '".$value."'";
        }
        $sql = "UPDATE ".$this->_sql['form']=substr($this->_sql['form'],5)." SET ".implode(", ",$a);
        unset($this->_sql['form']);
        unset($a);
        $sql .= " ".(implode(" ",$this->_sql));
        return $this->query($sql);
    }

    //自动关闭连接
    function __destruct()
    {
        mysqli_close($this->_conn);
    }



}
