<?php

namespace App\Http\Controllers\Code;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Storage;

class CodeController extends Controller
{
    /**获取accessToken*/
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
            //echo $response;echo '<hr>';
            $arr=json_decode($response,true);

            //accesstoken存缓存
            $key='aa';
            Redis::set($key,$arr['access_token']);
            //Redis::get($key);
            Redis::expire($key,3600);
            $token=$arr['access_token'];
            //print_r($token);
        }
        return $token;



    }

    public function getJsapiTicket()
    {
        $key = 'wx_jsapi_ticket';
        $ticket = Redis::get($key);
        if($ticket){
            return $ticket;
        }else{
            $access_token = $this->accessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$access_token&type=jsapi";
            $ticket_info = json_decode(file_get_contents($url),true);
            if(isset($ticket_info['ticket'])){
                Redis::set($key,$ticket_info['ticket']);
                Redis::expire($key,3600);
                return $ticket_info['ticket'];
            }else{
                return false;
            }
        }
    }

    //临时二维码
    public function code(){
        $accessToken=$this->accessToken();
        $ticket1=$this->getJsapiTicket();

        $nonceStr = Str::random(10);
        $time = time();
        $current_url = "https" . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
//        print_r($_SERVER['REQUEST_SCHEME']);exit;
        $string1 = "jsapi_ticket=$ticket1&noncestr=$nonceStr&timestamp=$time&url=$current_url";
        $sign = sha1($string1);
        $signInfo=[
            'appId'=>'wxdd0d451ebdddd4f9',
            'timestamp'=>$time,
            'nonceStr'=>$nonceStr,
            'signature'=>$sign,
        ];


        $url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$accessToken";
        $arr=array(
            "expire_seconds"=>604800,
            "action_name"=>"QR_STR_SCENE",
            "action_info"=>array(
                "scene"=>array(
                    "scene_id"=>"AA"
                )
            )
        );
        $str=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $client=new Client();
        $response=$client->request("POST",$url,[
            'body'=>$str
        ]);
        $obj=$response->getBody();
        $info=json_decode($obj,$url);
        $ticket=$info['ticket'];

        //通过ticket换取二维码
        $ticketUrl="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
        return view('code.code',['signInfo'=>$signInfo,'ticketUrl'=>$ticketUrl]);

    }
}
