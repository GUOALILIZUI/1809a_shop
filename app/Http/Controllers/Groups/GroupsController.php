<?php

namespace App\Http\Controllers\Groups;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Storage;

class GroupsController extends Controller
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
    /**openid群发*/
    /*public function GroupsUser(){
        $access=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=$access&next_openid=";
        $response=file_get_contents($url);
        $info=json_decode($response,true);
        $openIdInfo=$info['data']['openid'];
        //print_r($openIdInfo);
        $data=[];
        foreach($openIdInfo as $k=>$v){
            $userUrl="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access&openid=$v&lang=zh_CN";
            $UserResponse=file_get_contents($userUrl);
            $userInfo=json_decode($UserResponse,true);
            $data[]=$userInfo;
        }
        return view('groups.groups',['data'=>$data]);
    }
    */
    public function GroupsUser(){
        //$info=DB::table('wx')->get();
        //$openid=$info->openid;
        $access=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=$access&next_openid=";
        $strObj=file_get_contents($url);
        $info=json_decode($strObj,true);
        $openIdInfo=$info['data']['openid'];
        $openIdUrl="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$access";
        $arr=array(
            "touser"=>$openIdInfo,
            "msgtype"=>"text",
            "text"=>array(
                "content"=>"小鸭子嘎嘎嘎嘎"
            )
        );
        $groupsStr=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $client=new Client();
        $response=$client->request('POST',$openIdUrl,[
            'body'=>$groupsStr
        ]);
        $groupsObj=$response->getBody();
        $newInfo=json_decode($groupsObj,true);
        print_r($newInfo);

    }
}
