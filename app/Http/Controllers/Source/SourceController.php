<?php

namespace App\Http\Controllers\Source;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class SourceController extends Controller
{
    //
    /**获取accessToken*/
    public function accessToken(){
        $key='aa';
        $token=Redis::get($key);
        if($token){

        }else{
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID_0').'&secret='.env('WEIXIN_MCH_KEY').'";
            //var_dump($we);
            $response=file_get_contents($url);
            //echo $response;echo '<hr>';
            $arr=json_decode($response,true);

            //accesstoken存缓存
            $key='aa';
            Redis::set($key,$arr['access_token']);
            //Redis::get($key);
            Redis::expire($key,3600);
            $token=$arr['access_token'];
            print_r($token);
       }
        return $token;



    }

    //上传素材
    public function index(){
        return view('source.index');
    }

    //素材接口
    public function source(){
        $accessToken=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$accessToken&type=image";

    }

}
