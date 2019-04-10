<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        var_dump($xmlObj);
        //echo 'success';
    }

    //获取accessToken
    public function accessToken(){
        $key='aa';
        $token=Redis::get($key);
        if($token){

        }else{
            $appId="wxdd0d451ebdddd4f9";
            $app_secret="3a0980e46f62a1f9b759fa11adaab484";
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$app_secret";
            //var_dump($url);
            $response=file_get_contents($url);
            echo $response;echo '<hr>';
            $arr=json_decode($response,true);

            //accesstoken存缓存
            $key='aa';
            Redis::set($key,$arr['access_token']);
            //Redis::get($key);
            Redis::expire($key,3600);
            $token=$arr['access_token'];
        }
        return $token;



    }

}
