# WXSDK
#使用说明
--
调用WXSDK前，先将TOKEN、APPID和APPSECRET传入其中
```php
include 'WXSDK.php';
header("Content-type: text/html; charset=utf-8");
$token = '';
$appid = '';
$appsecret = '';
$wx=new WXSDK($token,$appid,$appsecret);
```
# #主要包含的功能
<br/><br/><br/>
（1）微信接入和相关信息回复（默认）
```php
$wx->valid()->responseMsg();
```
<br/><br/><br/>
（2）自定义关键词回复及其相对应的内容（_setKeywords为自定义关键词及其相对应的内容，数组形式）
```php
$wx->_setKeywords([
    'hello'=>"hello world! thanks your subscribe"
])
    ->valid()
    ->responseMsg();
```
<br/><br/><br/>
（3）菜单的创建、查询、删除
```php
/**
     * 设置菜单
     *
     * @param null $postmenu (请按照微信菜单的格式填写菜单)
     * @return bool
     */
//创建菜单 页面显示true则菜单创建成功
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
$res=$wx->createMenu($postmenu);
var_dump($res);

//删除菜单 页面显示true则菜单删除成功
$res=$wx->deleteMenu();
var_dump($res);

//查询菜单
$res=$wx->selectMenu();
var_dump($res);
```
<br/><br/><br/>
（4）获取调用jssdk前所需要的配置项
```php
$res=$wx->getSignPackage();
var_dump($res);
```
<br/><br/><br/>
（5）oauth2授权获取用户相关信息（oauth2URL为拼接符合微信oauth2授权的URL）<br/>
在index.php中
```php
/**
     * oauth2授权连接
     *
     * @param $redirect_uri (要跳转到URL)
     * @param bool $snsapi  (授权模式，默认为base型：获取openid；填写true为user_info型：获取用户的openid,头像，呢称，地区)
     * @param int $state  （重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值，最多128字节）
     * @return string 生成拼接完的URL
     */
$url=$wx->oauth2URL('http://xxxx.php'，true，123);
$wx->_setKeywords(['oauth'=>"<a href='$url'>oauth2授权获取头像</a>"])->valid()->responseMsg();
```
在xxx.php中
```php
/**
     * user_info型授权（获取用户的openid,头像，呢称，地区）
     *
     * @return array|bool|string  (返回数组[info]为二维数组,若调用oauth2时带了参数则有[state])
     */
$res=$wx->get_user_info();
$img = $res['info']['headimgurl'];
```

（6）模板推送功能
```php
/**
     * 消息模板推送函数
     *
     * @param $user_id （被推送用户的openid）
     * @param $template_id （消息模板的id，这个得在文档找你需要的）
     * @param $redirect_uri （点开详情后要跳转的url）
     * @param $color     （顶部颜色）
     * @param $content   （模板的详细内容，数组格式，请根据id对应的模板内容格式填写，{{xxx.DATA}}）
     * @return bool|string
     */
//假设模板内容为：hello {{name.DATA}},Do you want to buy a {{store.DATA}}?
$user_id = "xxxxxxxxxxxxx";
$template_id="pibLvCEBn4P4k44g3Pvff110A8zVl7Q42mHCUSDNw4w";
$redirect_uri="http://www.baidu.com/";
$color="#FF0000";
$content=[
    "name"=>[
        "value" => "marcus",
        "color" => "#173177"
    ],
    "store"=>[
        "value" => "coffee",
        "color" => "#173177"
    ],
];
$res=$wx->sendTemplate($user_id,$template_id,$redirect_uri,$color,$content);
var_dump($res);
```

（7）获取关注者列表功能
```php
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
$res = $wx->get_user_list();
var_dump($res);
```


（8）发送文本信息给指定用户功能
```php
/**
     * 发送文本信息给指定用户（利用客服接口，在48个小时内不限制发送次数）
     *
     * @param $toOpenid （普通用户的OpenID）
     * @param string $content （文本消息内容）
     * @return array|bool|string （返回数组，["error"]=200为发送成功）
     */
$openid = "xxxxxxxxxxxxxx";
$content = "您好";
$res = $wx->push_message($openid,$content);
var_dump($res);
//发送超链接也可以
$openid = "xxxxxxxxxxxxxx";
$url = "http://www.baidu.com";
$content = "<a href='$url'>点击跳转到百度</a>";
$res = $wx->pushMessage($openid,$content);
var_dump($res);
```

（9）发送图文信息给指定用户功能
```php
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
$openid = "xxxxxxxxxxxxxx";
$title = "百度"；
$description = "百度一下，你就知道"；
$picurl = "https://ss0.bdstatic.com/5aV1bjqh_Q23odCf/static/superman/img/logo/bd_logo1_31bdc765.png"
$url = "http://www.baidu.com/"
$res = $wx->pushNews($openid,$title,$description,$picurl,$url);
var_dump($res);
//只要将$picurl传空值，$description填充更具体的业务内容
//发送图文信息就变成类似模板推送信息
$openid = "xxxxxxxxxxxxxx";
$timenow =time();
$title = "排号提醒";
$description = "\n商家名称：到本首饰\n排队号码：A50\n前面等待：10\n预计等待：……\n排队状态：排队中\n你可以点击下方菜单‘我的菜单’查看\n当前时间{$timenow}
";
$picurl = "";
$url = "http://www.baidu.com/";
$res = $wx->pushNews($openid,$title,$description,$picurl,$url);
var_dump($res);
```