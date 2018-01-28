<?php
//staffSDK使用即时客服服务
include 'WXSDK/staffSDK.php';
header("Content-type: text/html; charset=utf-8");
$token = 'weixin';
$appid = 'wxb943a262983ddef6';
$appsecret = 'a717afac1add14e5f7a8a13c72c0196a';
$wx=new staffSDK($token,$appid,$appsecret);
$wx->_setStaffsServer(true)//开启客服服务
    ->_setStaffs(["oBnml0vE4dzB9WPUp1y2JEJlE_bU"])//设置客服openid
    ->valid()//接入微信
    ->responseMsg();