<?php
define("TOKEN","weixin");
define("APPID","");
define("APPSECRET","");
$wx=new WeChatApi(TOKEN,APPID,APPSECRET);
$wx->valid();

$mmc=new Memcache();
$mmc->connect('');
$mmc->set('name','marcus',0,10);
echo $mmc->get('name');







class WeChatApi
{
    private $token;
    private $appId;
    private $appSecret;

    public function __construct($token="",$appID="",$appSecret="")
    {
        $this->token=$token;
        $this->appId=$appID;
        $this->appSecret=$appSecret;
    }

    function valid(){
        if ($this->checkSignature())
        {
            $this->responseMsg();
        } else{
            echo 'failed';
        }
    }

    //验证微信签名
    private function checkSignature()
    {
        $timestamp = $_GET['timestamp'];//时间戳
        $nonce = $_GET['nonce'];//随机数
        $token = $this->token;//口令
        $signature = $_GET['signature'];//微信加密签名
        $echostr = $_GET['echostr'];//随机字符串

        //开始加密校验
        //1.将$timestamp,$nonce,$token三个参数进行字典序排序
        $array = array($timestamp, $nonce, $token);
        sort($array);
        //2.将排序后的三个参数拼接之后用sha1加密
        $tmpstr = implode('', $array);
        $tmpstr = sha1($tmpstr);
        //3.将加密后的字符串与signature进行对比，判断该请求是否来自微信
        if ($tmpstr == $signature && $echostr) {
            //第一次接入微信api接口的时候
            echo $echostr;
            exit();
        } else {
            return true;
        }
    }
    //消息的回复
    private function responseMsg()
    {
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];  //预定义变量，获取原生POST数据
        $postObj = simplexml_load_string($postArr);  //把xml字符串载入到对象中，如果失败，则返回false
        $msgType=$postObj->MsgType;//消息类型
        //根据消息类型进行业务处理
        switch (strtolower($msgType)){
            //处理关键词文本类型业务
            case 'text':
                $content=trim($postObj->Content);//获取文本消息内容
                switch ($content){
                    case '单图文':
                        $arr=array(
                            array(
                                'Title'=>'lambda官网',
                                'Description'=>'澜达网络公司介绍',
                                'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                                'Url'=>'http://www.lambdass.com/'
                            )
                        );
                        $this->responseNews($postObj,$arr);
                        break;
                    case '多图文':
                        $arr=array(
                            array(
                                'Title'=>'lambda官网',
                                'Description'=>'澜达网络公司介绍',
                                'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                                'Url'=>'http://www.lambdass.com/',
                            ),

                            array(
                                'Title'=>'百度',
                                'Description'=>'百度一下，你就知道',
                                'PicUrl'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png',
                                'Url'=>'http://www.baidu.com/',
                            ),

                            array(
                                'Title'=>'慕课',
                                'Description'=>'程序员的梦工厂',
                                'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                                'Url'=>'http://www.imooc.com/',
                            )
                        );
                        $this->responseNews($postObj,$arr);
                        break;
                    case "你好":
                        $content = '你好，谢谢您的订阅';
                        $this->responseText($postObj,$content);
                        break;
                    case "login":
                        $content = 'OAuth2.0网页授权演示
    <a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxb943a262983ddef6&redirect_uri=http://demo.lambdass.com/Weixin/wx/oauth2.php&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect">点击这里体验</a>
    技术支持';
                        $this->responseText($postObj,$content);
                        break;
                    case "hello":
                        $content = 'hello world! thanks your subscribe';
                        $this->responseText($postObj,$content);
                        break;
                    case "新垣结衣":
                        $content = '对，头像的就是她，我女神，好美是不是';
                        $this->responseText($postObj,$content);
                        break;
                    case "百度":
                        $content = "<a href='http://www.baidu.com'>点击跳转到百度页面</a>";
                        $this->responseText($postObj,$content);
                        break;
                    case preg_match("/^cxwz([\\x{4e00}-\\x{9fa5}]+)/ui",$content,$res)://要求用户输入cxwz+地方名称
                        $address=$res[1];
                        //这里从数据库当中获取该用户的刚刚上传的经纬度，如果查询失败，就发送消息说未上传
                        $longitude='经度';
                        $latitude='纬度';
                        $content="请点击该连接，即可查询该地点的信息\r\n\r\n 
                        http://api.map.baidu.com/place/search?
                        query=".urlencode($address)."&location={$latitude},{$longitude}
                        &radius=1000&output=html&coord_type=gcj02";
                        $this->responseText($postObj,$content);
                        break;
                }
                break;
            //处理事件类型业务
            case 'event':
                $event=trim($postObj->Event);//获取事件具体消息
                switch ($event){
                    case 'subscribe':
                        if(substr($postObj->EventKey,0,8)=='qrscene_'){//之前未关注，现通过带参数的扫码关注
                            $content='hello！欢迎您通过扫码关注我的公众号';
                            $this->responseText($postObj,$content);
                            /*
                                此处你可以进行数据库操作
                                插入  value值=$postObj->EventKey=qrscene_生成参数
                              */
                        }else{
                            $content='hello！欢迎关注我的公众号';
                            $this->responseText($postObj,$content);
                        }
                        break;
                    case 'unsubscribe':
                        //账号解绑
                        break;
                    case 'SCAN'://之前已经关注，现通过带参数扫码进入公众号
                        $content='您之前已经关注过我的公众号';
                        $this->responseText($postObj,$content);
                        break;
                    case 'CLICK'://自定义菜单事件
                        switch ($postObj->EventKey){
                            case 'LAMBDA'://此处与自定义菜单接口的KEY值相对应
                                $arr=array(
                                    array(
                                        'Title'=>'lambda官网',
                                        'Description'=>'澜达网络公司介绍',
                                        'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                                        'Url'=>'http://www.lambdass.com/'
                                    )
                                );
                                $this->responseNews($postObj,$arr);
                                break;
                        }
                        break;
                }
                break;
            //处理地理位置类型业务
            case 'location':
                //获取经度和纬度
                $Location_Y=$postObj->Location_Y;
                $Location_X=$postObj->Location_X;
                $content = "您好，我们已经收到您上报的地理位置\r\n\r\n
                经度为{$Location_Y}\r\n纬度为{$Location_X}\r\n\r\n
                请您输入您关心的地方，即可查询";
                $this->responseText($postObj,$content);
                //将经纬度入库，以防日后用到
                //如果用户数据不存在，则创建用户，否则直接更新保存
                break;
            //处理类型业务
            case  '':
                break;

        }
    }

    //回复（多/单）图文类型的微信消息
    public function responseNews($postObj,$arr){
        $toUser   =$postObj->FromUserName;
        $fromUser =$postObj->ToUserName;

        $template ="<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <ArticleCount>".count($arr)."</ArticleCount>
                        <Articles>";

        foreach ($arr as $k=>$v) {
            $template .= "<item>
                        <Title><![CDATA[".$v['Title']."]]></Title>
                        <Description><![CDATA[".$v['Description']."]]></Description>
                        <PicUrl><![CDATA[".$v['PicUrl']."]]></PicUrl>
                        <Url><![CDATA[".$v['Url']."]]></Url>
                        </item>";
        }

        $template .="</Articles>
                        </xml>";

        $time     =time();
        $msgType  ='news';
        $info     =sprintf($template,$toUser,$fromUser,$time,$msgType);
        echo $info;
    }
    //回复文本类型的微信消息
    public function responseText($postObj,$content){

        $template ="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            </xml>";

        $toUser   =$postObj->FromUserName;
        $fromUser =$postObj->ToUserName;
        $time     =time();
        $msgType  ='text';
        $info     =sprintf($template,$toUser,$fromUser,$time,$msgType,$content);
        echo $info;
    }
    //发起HTTPS请求，返回数组
    public function https_request($url,$data=null){
        $curl=curl_init();
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,FALSE);//用于验证第三方服务器与微信服务器的安全性，若在SAE，BAE平台则不需要
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,FALSE);//用于验证第三方服务器与微信服务器的安全性，若在SAE，BAE平台则不需要
		if(!empty($data)){
            curl_setopt($curl,CURLOPT_POST,1);//模拟post请求
            curl_setopt($curl,CURLOPT_POSTFIELDS,$data);//post提交数据
        }
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);//将页面以文件流的形式保存，1为不显示数据在页面上
        $output= curl_exec($curl);
        if(curl_error($curl)){ return 'ERROR'.curl_error($curl); }//输出错误信息
        curl_close($curl);
        return json_decode($output,true);//将json转换成数组格式返回，第二个参数默认返回为对象，true为返回数组
        }
    //获取access_token（接口调用凭证）
    public function getAccessToken(){
        //判断有无缓存access_token
        if ((!$this->_memcache_get('access_token')) || (!$_SESSION['access_token']))
        {
            $appID=$this->appId;
            $appSecret=$this->appSecret;
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appID}&secret={$appSecret}";
            $result=$this->https_request($url);
            if (!isset($result['errcode'])){
                //判断memcache缓存access_token是否成功
                if ($this->_memcache_set('access_token',$result['access_token'],7000)){
                    $res=$this->_memcache_get('access_token');
                }else{
                    //改用session存储access_token
                    session_start();
                    $_SESSION['access_token']=$result['access_token'];
                    $res=$_SESSION['access_token']?$_SESSION['access_token']:false;
                }
            }else{
                $res=false;
            }
        }else{
            $res=$_SESSION['access_token']?$_SESSION['access_token']:$this->_memcache_get('access_token');
        }
        return $res;
    }
    //设置菜单
    public function createMenu(){
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        $postmenu='{
                    "button":[
                     {    
                          "name":"关于我们",
                          "sub_button":[
                           {    
                               "type":"view",
                               "name":"官网地址",
                               "url":"http://www.lambdass.com"
                            },
                            {
                               "type":"click",
                               "name":"澜达网络",
                               "key":"LAMBDA"
                            }]
                      },
                      {
                           "name":"走全国",
                           "type":"view",
                           "url":"http://www.moocba.com"
                      },
                      {
                           "name":"成功项目",
                           "sub_button":[
                           {    
                               "type":"view",
                               "name":"搜索",
                               "url":"http://www.soso.com/"
                            },
                            {
                               "type":"view",
                               "name":"视频",
                               "url":"http://v.qq.com/"
                            },
                            {
                               "type":"click",
                               "name":"赞一下我们",
                               "key":"LIKE"
                            }]
                       }]
                  }';
        $result=$this->https_request($url,$postmenu);
        if (!$result['errcode']){
            $res=true;
        }else{
            $res=$result['errmsg'];
        }
        return $res;
    }
    //查询菜单
    public function selectMenu(){
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$access_token}";
        return $this->https_request($url);
    }
    //删除菜单
    public function deleteMenu(){
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$access_token}";
        $result=$this->https_request($url);
        if (!$result['errcode']){
            $res=true;
        }else{
            $res=$result['errmsg'];
        }
        return $res;
    }
    //base型授权（只获取用户的openid）
    public function snsapi_base($redirect_uri,$state=123){//重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
        $appID=$this->appId;
        $appSecret=$this->appSecret;
        $redirect_uri=urlencode($redirect_uri);
        //准备Scope为snsapi_base的网页授权页面URL
        $snsapi_base_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appID}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
        //静默授权，获取code:然后页面会跳转至redirect_uri={redirect_uri}/?code=CODE&state={$state}
        if (!isset($_GET['code']))//如果获取不到，在重定向一次去获取code
        {
            header("Location:{$snsapi_base_url}");
        }
        $code=$_GET['code'];
        //通过code获取网页授权access_token
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
        if ($this->https_request($url)){
            $res=$this->https_request($url);
            $res=$res['openid'];
        }else{
            $res=false;
        }
        return $res;
    }
    //user_info型授权（获取用户的openid,头像，呢称，地区）
    public function snsapi_user_info($redirect_uri,$state=123){//重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
        $appID=$this->appId;
        $appSecret=$this->appSecret;
        $redirect_uri=urlencode($redirect_uri);
        //准备Scope为snsapi_userinfo的网页授权页面URL
        $snsapi_user_info_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appID}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
        //用户手动同意授权，获取code:同意则然后页面会跳转至redirect_uri={redirect_uri}/?code=CODE&state={$state}
        if (!isset($_GET['code']))//如果获取不到，在重定向一次去获取code
        {
            header("Location:{$snsapi_user_info_url}");
        }
        $code=$_GET['code'];
        //通过code获取网页授权access_token
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
        $result=$this->https_request($url);
        if ($result){
            $res=false;
        }elseif ($result['errcode']){
            $res='get access_token false:'.$result['errmsg'];
        }else{
            $access_token=$result['access_token'];
            $open_id=$result['openid'];
            //根据上一步获取的access_token和openid拉取用户信息
            $info_url="https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $result=$this->https_request($info_url);
            if ($result){
                $res=false;
            }elseif ($result['errcode']){
                $res='get user_info false:'.$result['errmsg'];
            }else{
                $res=$result;
            }
        }
        return $res;
    }

    //实例化memcache
    public function _memcache_init($host=''){
        $mmc=new Memcache();
        $mmc->connect($host);
        return $mmc;
    }
    //设置memcache
    public function _memcache_set($key,$value,$time=0){//$time=0为该set永远有效
        $mmc=$this->_memcache_init();
        if ($mmc->set($key,$value,0,$time)){
            $res=true;
        }else{
            $res=false;
        }
        return $res;
    }
    //获取memcache
    public function _memcache_get($key)
    {
        $mmc = $this->_memcache_init();
        return $mmc->get($key);
    }
}



