<?php
require_once ("BaseSDK.php");
class cardSDK extends BaseSDK
{
    public function uploadLogo($path){
        $path = dirname(__FILE__)."\\".$path;
        $filedata = ["media" => "@".$path];
        $access_token=$this->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token={$access_token}";
        $result = $this->https_request($url,$filedata);
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