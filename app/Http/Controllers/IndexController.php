<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    //测试
    public function Info(){
        phpinfo();
    }

    //接口配置
    public function check(Request $request){
        //首次接入检测请求是否为微信
        //echo $request->input('echostr');

        $content=file_get_contents("php://input");
        $time=date("Y-m-d H:i:s");
        $str=$time.$content.'\n';
        file_put_contents("logs/check.log",$str,FILE_APPEND);
        $xmlObj=simplexml_load_string($content);

        $ToUserName=$xmlObj->ToUserName;
        $FromUserName=$xmlObj->FromUserName;
        $CreateTime=$xmlObj->CreateTime;
        $MsgType=$xmlObj->MsgType;
        $Event=$xmlObj->Event;

        $access=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access&openid=$FromUserName&lang=zh_CN";
        $response=file_get_contents($url);
        $info=json_decode($response,true);
        //$name=$info['nickname'];
        print_r($info);exit;
        if($Event='subscribe'){
           $data=DB::table('wx')->where('openid',$FromUserName)->count();
           //print_r($data);die;
           if($data=='0'){
               $weiInfo=[
                   'name'=>$name,
                   'sex'=>$info['sex'],
                   'img'=>$info['headimgurl'],
                   'openid'=>$info['openid'],
                   'time'=>time()
               ];
               //print_r($weiInfo);
               DB::table('wx')->insert($weiInfo);

               //回复消息
               $time=time();
               $content="关注本公众号成功";
               $xmlStr="
                   <xml>
                        <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                        <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                        <CreateTime>$time</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[$content]]></Content>
                   </xml>";
               echo $xmlStr;

           }else{
               $time=time();
               $content="欢迎".$name."回来";
               $xmlStr="
                   <xml>
                        <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                        <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                        <CreateTime>$time</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[$content]]></Content>
                   </xml>
               ";
               echo $xmlStr;
           }

        }





    }

    //获取accessToken
    public function accessToken(){
        $key='aa';
        $token=Redis::get($key);
        //if($token){

        //}else{
            $appId="wxdd0d451ebdddd4f9";
            $app_secret="3a0980e46f62a1f9b759fa11adaab484";
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$app_secret";
            //var_dump($url);
            $response=file_get_contents($url);
            //echo $response;echo '<hr>';
            $arr=json_decode($response,true);

            //accesstoken存缓存
            $key='aa';
            Redis::set($key,$arr['access_token']);
            //Redis::get($key);
            Redis::expire($key,3600);
            $token=$arr['access_token'];
            //print_r($token);
        //}
        return $token;



    }

}
