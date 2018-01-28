<?php
class BaseSDK
{
    private $token;
    private $appId;
    private $appSecret;
    private $keywords = [
        "openid" => "您的openid是:",
        "voice"=>"",
        "photo"=>"",
        "你好" => '你好，谢谢您的订阅',
        "hello" => 'hello world! thanks your subscribe',
        "百度" => "<a href='http://www.baidu.com'>点击跳转到百度页面</a>",
        '单图文' => [
            [
                'Title'=>'lambda官网',
                'Description'=>'澜达网络公司介绍',
                'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                'Url'=>'http://www.lambdass.com/'
            ]
        ],
        '多图文' => [
            [
                'Title'=>'lambda官网',
                'Description'=>'澜达网络公司介绍',
                'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                'Url'=>'http://www.lambdass.com/',
            ],
            [
                'Title'=>'百度',
                'Description'=>'百度一下，你就知道',
                'PicUrl'=>'https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png',
                'Url'=>'http://www.baidu.com/',
            ],
            [
                'Title'=>'慕课',
                'Description'=>'程序员的梦工厂',
                'PicUrl'=>'http://www.imooc.com/static/img/index/logo_new.png',
                'Url'=>'http://www.imooc.com/',
            ]
        ]
    ];
    private $subscribeContent = "hello！欢迎关注我的公众号";
    protected $keyEvents = [
        "LAMBDATUWEN" => [
            [
                'Title'=>'lambda官网',
                'Description'=>'澜达网络公司介绍',
                'PicUrl'=>'http://www.lambdass.com/index_files/640.jpg',
                'Url'=>'http://www.lambdass.com/'
            ]
        ]
    ];

    public function __construct($token="",$appID="",$appSecret="")
    {
        $this->token=$token;
        $this->appId=$appID;
        $this->appSecret=$appSecret;
    }


    //接入微信
    public function valid(){
        if ($this->checkSignature())
        {
            return $this;
        }
        else
        {
            return 'invalid!';
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
    public function responseMsg()
    {
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];  //预定义变量，获取原生POST数据
        if (!empty($postArr))
        {
            $postObj = simplexml_load_string($postArr);  //把xml字符串载入到对象中，如果失败，则返回false
            $msgType=$postObj->MsgType;//消息类型
            $result = null;
            //根据消息类型进行业务处理
            switch (strtolower($msgType)){
                //处理关键词文本类型业务
                case 'text':
                    $result = $this->receiveText($postObj);
                    break;
                //处理事件类型业务
                case 'event':
                    $result = $this->receiveEvent($postObj);
                    break;
                //处理地理位置类型业务
                case 'location':
                    $result = $this->receiveLocation($postObj);
                    break;
                //处理图片类型业务
                case 'image':
                    $result = $this->receiveImage($postObj);
                    break;
                //处理声音类型业务
                case 'voice':
                    $result = $this->receiveVoice($postObj);
                    break;
                default:
                    $result =$this->responseText($postObj,"没有该服务");
                    break;
            }
            echo $result;
        }
        else
        {
            echo '';
            exit;
        }

    }
    //设置关键字及相对应的内容
    public function _setKeywords($keywords = [])
    {
        if (!empty($keywords)){
            $this->keywords = $keywords;
        }
        return $this;
    }
    //设置关注后自动回复的信息
    public function _setSubscribe($content = '')
    {
        if (!empty($content)){
            $this->subscribeContent = $content;
        }
        return $this;
    }
    //设置自定义菜单事件的相对应的KEY值和相对应的内容
    public function _setClick($keyEvent = [])
    {
        if (!empty($keyEvent)){
            $this->keyEvents = $keyEvent;
        }
        return $this;
    }
    //关键字回复处理函数
    protected function receiveText($object)
    {
        $content = trim($object->Content);
        $keywords = $this->keywords;
        $reply1 = null;
        $reply2 = null;
        $reply3 = null;
        $reply4 = null;
        foreach ($keywords as $key=>$value)
        {
            //判断回复的关键字是否在关键字集当中
            if (strchr($content,$key))
            {
                if (strchr($content,'图文')){
                    $reply1 = $value;
                    break;
                }
                elseif (strchr($content,'photo'))
                {
                    if (strlen($content)>5){
                        $reply2 = substr($content,5);
                    }else{
                        $reply2 = $value;
                    }
                    break;
                }
                elseif (strchr($content,'voice'))
                {
                    if (strlen($content)>5){
                        $reply3 = substr($content,5);
                    }else{
                        $reply3 = $value;
                    }
                    break;
                }
                elseif(strchr($content,'openid'))
                {
                    $reply4 = $value." ".$object->FromUserName;
                    break;
                }
                else
                {
                    $reply4 = $value;
                    break;
                }
            }
        }
        if ($reply1){
            $result = $this->responseNews($object,$reply1);
        }elseif ($reply2){
            $result = $this->responseImage($object,$reply2);
        }elseif ($reply3){
            $result = $this->responseVoice($object,$reply3);
        }else{
            $reply4 = $reply4 ? $reply4:"抱歉，请求的资源不允许";
            $result = $this->responseText($object,$reply4);
        }
        return $result;
        
    }
    //接收事件处理函数
    protected function receiveEvent($object)
    {
        $result = '';
        switch (trim($object->Event)) {
            case 'subscribe':
                if (substr($object->EventKey, 0, 8) == 'qrscene_') {//之前未关注，现通过带参数的扫码关注
                    $content = 'hello！欢迎您通过扫码关注我的公众号';
                    $result = $this->responseText($object, $content);
                    /*
                        此处你可以进行数据库操作
                        插入  value值=$postObj->EventKey=qrscene_生成参数
                      */
                } else {
                    $content = $this->subscribeContent;
                    $result = $this->responseText($object, $content);
                }
                break;
            case 'unsubscribe':
                $content = "取消关注";
                $result = $this->responseText($object, $content);
                //账号解绑
                break;
            case 'SCAN'://之前已经关注，现通过带参数扫码进入公众号
                $content = '您之前已经关注过我的公众号';
                $result = $this->responseText($object, $content);
                break;
            case 'CLICK'://自定义菜单事件
                $result = $this->receiveKeyEvent($object);
                break;
        }
        return $result;
    }
    //接收位置信息处理函数
    protected function receiveLocation($object)
    {
        $Location_Y=$object->Location_Y;
        $Location_X=$object->Location_X;
        $content = "您好，我们已经收到您上报的地理位置\r\n\r\n
                经度为{$Location_Y}\r\n纬度为{$Location_X}\r\n\r\n";
        //将经纬度入库，以防日后用到
        //如果用户数据不存在，则创建用户，否则直接更新保存
        $result=$this->responseText($object,$content);
        return $result;
    }
    //自定义菜单事件处理函数
    protected function receiveKeyEvent($object)
    {
        $content = trim($object->EventKey);
        $keyEvents = $this->keyEvents;
        $reply1 = null;
        $reply2 = null;
        foreach ($keyEvents as $key=>$value)
        {
            //判断发生的key值事件是否在事件集当中
            if (strchr($content,$key))
            {
                if (strchr($content,'TUWEN')){
                    $reply1 = $value;
                    break;
                }
                else
                {
                    $reply2 = $value;
                    break;
                }
            }
        }
        //若没有找到相关菜单定义事件
        $reply2 = $reply2 ? $reply2:"功能未开通";
        //若$reply1不为空则回复图文类型，否则回复文字类型
        $result = $reply1 ? $this->responseNews($object,$reply1) : $this->responseText($object,$reply2);
        return $result;
    }
    //接收图片处理函数
    protected function receiveImage($object)
    {
        $content="图片的临时链接为：".$object->PicUrl."\n图片的临时MediaId为".$object->MediaId;
        $result = $this->responseText($object,$content);
        return $result;
    }
    //接收语音处理函数
    protected function receiveVoice($object)
    {
        $content="语音的临时MediaId为".$object->MediaId;
        $result = $this->responseText($object,$content);
        return $result;
    }

    //回复（多/单）图文类型的微信消息
    protected function responseNews($postObj,$arr){
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
        return $info;
    }
    //回复文本类型的微信消息
    protected function responseText($postObj,$content){

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
        return $info;
    }
    //回复图片类型的微信消息(图片消息媒体id，可以调用多媒体文件下载接口拉取数据)
    protected function responseImage($postObj,$MediaId){

        $template ="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Image>
                            <MediaId><![CDATA[%s]]></MediaId>
                            </Image>
                            </xml>";

        $toUser   =$postObj->FromUserName;
        $fromUser =$postObj->ToUserName;
        $time     =time();
        $msgType  ='image';
        $info     =sprintf($template,$toUser,$fromUser,$time,$msgType,$MediaId);
        return $info;
    }
    //回复语音类型的微信消息(语音消息媒体id，可以调用多媒体文件下载接口拉取数据)
    protected function responseVoice($postObj,$MediaId){

        $template ="<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Voice>
                            <MediaId><![CDATA[%s]]></MediaId>
                            </Voice>
                            </xml>";

        $toUser   =$postObj->FromUserName;
        $fromUser =$postObj->ToUserName;
        $time     =time();
        $msgType  ='voice';
        $info     =sprintf($template,$toUser,$fromUser,$time,$msgType,$MediaId);
        return $info;
    }




    /**
     * 调用JSSDK前要获取的配置项
     *
     * @return array （返回数组：["appId"],["timestamp"],["nonceStr"],["signature"]）
     */
    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    /**
     * 创建加密字符串
     *
     * @param int $length
     * @return string
     */
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取jsapi_ticket
     *
     * @return mixed
     */
    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("./WXSDK/jsapi_ticket.json"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = $this->https_request($url);;
            $ticket = $res['ticket'];
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $fp = fopen("./WXSDK/jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }

    /**
     * 获取access_token（接口调用凭证,最好把token缓存起来）
     *
     * @return bool (获取成功则返回token，否则返回false)
     */

    protected function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("./WXSDK/access_token.json"));
        if ($data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = $this->https_request($url);
            $access_token = $res['access_token'];
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen("./WXSDK/access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

    /**
     * 设置菜单
     *
     * @param null $postmenu (请按照微信菜单的格式填写菜单，
     * 定义type为CLICK类型事，如果要响应图文事件，key一定得含有"TUWEN"字眼)
     * @return bool
     */

    public function createMenu($postmenu = null){
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";
        if (!$postmenu)
        {
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
                               "key":"LAMBDATUWEN"
                            },
                            {
                                "type":"click",
                                "name":"在线客服",
                                "key":"staffs"
                            }
                            ]
                      },
                      {
                           "name":"百度",
                           "type":"view",
                           "url":"http://www.baidu.com"
                      },
                      {
                           "name":"成功项目",
                           "sub_button":[
                           {    
                               "type":"view",
                               "name":"原本佛山",
                               "url":"http://www.ybfoshan.com/"
                            },
                            {
                               "type":"view",
                               "name":"佛大官网",
                               "url":"http://web.fosu.edu.cn/"
                            },
                            {
                               "type":"view",
                               "name":"凌达工作室",
                               "url":"http://web.fosu.edu.cn/lambda/"
                            }]
                       }]
                  }';
        }

        $result=$this->https_request($url,$postmenu);
        if (!$result['errcode']){
            $res=true;
        }else{
            $res=$result['errmsg'];
        }
        return $res;
    }

    /**
     * 查询菜单
     *
     * @return mixed|string
     */

    public function selectMenu(){
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token={$access_token}";
        return $this->https_request($url);
    }

    /**
     * 删除菜单
     *
     * @return bool
     */

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

    /**
     * 获取关注者列表
     *
     * @param null $next_openid (从这个关注者开始拉取往后的关注者，无则从第一个开始拉取)
     * @return array|string
     * {
    //关注该微信公众账号的总关注数
    "total" : 200,
    //拉取的OpenID个数，最大值为10 000
    "count" : 200,
    //列表数据，OpenID的列表
    "data" : {
    "openid" : [
    "hjfgwefgerigiue",
    ……………………
    "iouiojhrjktbhehu"
    ]
    },
    //拉取列表的最后一个关注者的OpenID
    "next_openid" : "iouiojhrjktbhehu"

     * }
     */
    public function get_user_list($next_openid = null)
    {
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&next_openid={$next_openid}";
        return $res = $this->https_request($url);
    }

    /**
     * oauth2授权连接
     *
     * @param $redirect_uri (要跳转到URL)
     * @param bool $snsapi  (授权模式，默认为base型：获取openid；填写true为user_info型：获取用户的openid,头像，呢称，地区)
     * @param int $state  （重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节）
     * @return string 生成拼接完的URL
     */

    public function oauth2URL($redirect_uri, $snsapi = false, $state=123)//重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节
    {
        $snsapi = $snsapi ? "snsapi_userinfo":"snsapi_base";
        $appID=$this->appId;
        $redirect_uri=urlencode($redirect_uri);
        //准备Scope为snsapi的网页授权页面URL
        $snsapi_url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appID}&redirect_uri={$redirect_uri}&response_type=code&scope={$snsapi}&state={$state}#wechat_redirect";
        return $snsapi_url;
    }

    /**
     * base型授权（只获取用户的openid）
     *
     * @return array|bool (返回数组[openid],若调用oauth2时带了参数则有[state])
     */

    public function get_user_openid(){
        $appID=$this->appId;
        $appSecret=$this->appSecret;
        if (!isset($_GET['code']))//如果获取不到，提示获取不到code
        {
            $res = ['error' => 404, 'info' => 'code catch failure'];
            return $res;
        }
        if (isset($_GET['state']))
        {
            $res['state'] = $_GET['state'];
        }
        $code=$_GET['code'];
        //通过code获取网页授权access_token
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
        if ($this->https_request($url)){
            $result=$this->https_request($url);
            $res['openid'] = $result['openid'];
        }else{
            $res=false;
        }
        return $res;
    }

    /**
     * user_info型授权（获取用户的openid,头像，呢称，地区）
     *
     * @return array|bool|string  (返回数组[info]为二维数组,若调用oauth2时带了参数则有[state])
     */

    public function get_user_info(){
        $appID=$this->appId;
        $appSecret=$this->appSecret;
        if (!isset($_GET['code']))//如果获取不到，提示获取不到code
        {
            $res = ['error' => 404, 'info' => 'code catch failure'];
            return $res;
        }
        if (isset($_GET['state']))
        {
            $res['state'] = $_GET['state'];
        }
        $code=$_GET['code'];
        //通过code获取网页授权access_token
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appID}&secret={$appSecret}&code={$code}&grant_type=authorization_code";
        $result=$this->https_request($url);
        if (!$result){
            $res = false;
        }elseif ($result['errcode']){
            $res='get access_token false:'.$result['errmsg'].' error:'.$result['errcode'];
        }else{
            $access_token=$result['access_token'];
            $open_id=$result['openid'];
            //根据上一步获取的access_token和openid拉取用户信息
            $info_url="https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $result=$this->https_request($info_url);
            if (!$result){
                $res=false;
            }elseif ($result['errcode']){
                $res='get user_info false:'.$result['errmsg'];
            }else{
                $res['info'] =  $result ;
            }
        }
        return $res;
    }

    /**
     * http请求（发起HTTPS请求，返回数组）
     *
     * @param $url （传入要发起请求的URL）
     * @param null $data （发起请求的post数据）
     * @return mixed|string  （返回数组）
     */
    protected function https_request($url,$data=null){
        $curl=curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
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

    /**
     * 微信模板消息接口调用的请求函数
     * @param $data
     * @return mixed|string
     */

    private function templateHTTP($data){
        $ACCESS_TOKEN = $this->getAccessToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$ACCESS_TOKEN);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $tmpInfo;
    }

    /**
     * 消息模板推送函数
     *
     * @param $user_id （被推送用户的openid）
     * @param $template_id （消息模板的id，这个得在文档找你需要的）
     * @param $redirect_uri （点开详情后要跳转的url）
     * @param $color     （顶部颜色）
     * @param $content   （模板的详细内容，数组格式，请根据id对应的模板内容格式填写，）
     * @return bool|string
     */
    function sendTemplate($user_id,$template_id,$redirect_uri,$color,$content){
        $data = [
            'touser' => $user_id, // openid是发送消息的基础
            'template_id' => $template_id, // 模板id
            'url' => $redirect_uri, // 点击跳转地址，跳转到具体信息页面
            'topcolor' => $color, // 顶部颜色
            'data' => $content
        ];
        //执行函数，发送模板消息
        $returnInfo = $this->templateHTTP(json_encode($data));

        $returnInfo =json_decode($returnInfo,true);

        if ($returnInfo['errmsg']=="ok") {
            return true;
        }elseif($returnInfo['errcode'] == 40001){
            $res='send Template false:'.$returnInfo['errmsg'].' error:'.$returnInfo['errcode'];
            return $res;
        }else{
            return false;
        }
    }

    /**
     * 发送文本信息给指定用户（利用客服接口，在48个小时内不限制发送次数）
     *
     * @param $toOpenid （普通用户的OpenID）
     * @param string $content （文本消息内容）
     * @return array|bool|string （返回数组，["error"]=200为发送成功）
     */
    public function pushMessage($toOpenid,$content = 'hello')
    {
        if (empty($toOpenid)){
            return $res = [
                'error' => 500,
                'info' => 'openid is require'
            ];
        }
        $access_token=$this->getAccessToken();
        $data = '{
            "touser" : "'.$toOpenid.'",
            "msgtype" : "text",
            "text" : 
            {
                "content" : "'.$content.'"
            }
        }';
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $result = $this->https_request($url,$data);
        if (!$result){
            $res=false;
        }elseif ($result['errcode']){
            $res='push message false:'.$result['errmsg'];
        }else{
            $res=['error' => 200, 'info' => $result["errmsg"]];
        }
        return $res;

    }

    /**
     * 发送图文信息给指定用户（利用客服接口，对单一用户次数约10次）
     *
     * @param $toOpenid（普通用户的OpenID）
     * @param string $title（图文标题）
     * @param string $description（图文描述）
     * @param string $picurl（图文图片的链接）
     * @param string $url（图文跳转的链接）
     * @return array|bool|string（返回数组，["error"]=200为发送成功）
     */
    public function pushNews($toOpenid,$title = "",$description = "",$picurl = "",$url = "")
    {
        if (empty($toOpenid)){
            return $res = [
                'error' => 500,
                'info' => 'openid is require'
            ];
        }
        $access_token=$this->getAccessToken();
        $template = '{
            "touser" : "%s",
            "msgtype" : "news",
            "news" : 
            {
                "articles" : [
            {
                "title": "%s",
                "description" : "%s",
                "picurl" : "%s",
                "url" : "%s",
            }
        ]
            }
        }';
        $data = sprintf($template,$toOpenid,$title,$description,$picurl,$url);
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $result = $this->https_request($url,$data);
        if (!$result){
            $res=false;
        }elseif ($result['errcode']){
            $res='push message false:'.$result['errmsg'];
        }else{
            $res=['error' => 200, 'info' => $result["errmsg"]];
        }
        return $res;

    }
    
}

