<?php

namespace App\Http\Controllers\Code;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
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
    //临时二维码
    public function code(){
        $accessToken=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$accessToken";
        $arr=array(
            "expire_seconds"=>604800,
            "action_name"=>"QR_SCENE",
            "action_info"=>array(
                "scene"=>array(
                    "scene_id"=>1
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
        print_r($ticketUrl);exit;
//        return view('code.code',['ticketUrl'=>$ticketUrl]);

    }
}
