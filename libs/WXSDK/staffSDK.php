<?php
require_once ("BaseSDK.php");
class staffSDK extends BaseSDK
{
    protected $keyEvents = [
        "staffs" =>""
    ];
    private $staffs = ["oBnml0pWYEdvJUBRO_1zPaRQ8em0"];
    private $staffsServerSet = false;
    //设置客服的openid号
    public function _setStaffs($staffs = [])
    {
        if (!empty($staffs)){
            $this->staffs = $staffs;
        }
        return $this;
    }
    //设置是否开启客服接口服务
    public function _setStaffsServer($staffsServerSet = false)
    {
        if ($staffsServerSet){
            $this->staffsServerSet = true;
        }
        return $this;
    }
    //根据点击菜单的用户来判断身份
    private function staffServerStart($object)
    {
        //客服点击，结束会话
        if (in_array($object->FromUserName,$this->staffs))
        {
            $from = strval($object->FromUserName);
            $data = json_decode(file_get_contents("./WXSDK/staffs_talk.json"),true);
            $to = $data['from'];
            $this->pushMessage($from,"你已经结束本次对话！");
            $this->pushMessage($to,"感谢您的咨询。期待下次再会！");
            $fp = fopen("./WXSDK/staffs_talk.json", "w");
            fwrite($fp,"");
            fclose($fp);
        }
        else
        {
            $from = strval($object->FromUserName);
            $to = $this->staffs[array_rand($this->staffs,1)];
            $data=["from"=>$from,"to"=>$to];
            $fp = fopen("./WXSDK/staffs_talk.json", "w");
            fwrite($fp, json_encode($data));
            fclose($fp);
            $this->pushMessage($from,"正在为您接入客服，请稍后……");
            $this->pushMessage($to,"有用户请求接入，请响应！");
        }
    }
    //客服和用户的消息中转逻辑
    private function staffServering($object,$content=null)
    {
        $data = json_decode(file_get_contents("./WXSDK/staffs_talk.json"),true);
        if ($data)
        {
            if (in_array($object->FromUserName,$data))
            {
                $to = ($data['from'] == strval($object->FromUserName))
                    ?$data['to'] : $data['from'];
                $this->pushMessage($to,$content);
            }
        }
    }
    protected function receiveKeyEvent($object)
    {
        $content = trim($object->EventKey);
        $keyEvents = $this->keyEvents;
        $reply = null;
        foreach ($keyEvents as $key=>$value)
        {
            //判断发生的key值事件是否在事件集当中
            if (strchr($content,$key))
            {
                if(strchr($content,'staffs'))
                {
                    if ($this->staffsServerSet){
                        $this->staffServerStart($object);
                        $reply="";
                    }
                    break;
                }
                else
                {
                    $reply = "功能未开通";
                    break;
                }
            }
        }
        if($reply){
            $result =$this->responseText($object,$reply);
            return $result;
        }else{
            return "";
        }
    }
    protected function receiveText($object){
        $content = trim($object->Content);
        if($this->staffsServerSet)
        {
            $this->staffServering($object,$content);
        }
    }
}