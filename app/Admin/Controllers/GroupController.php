<?php

namespace App\Admin\Controllers;

use App\Model\SourceModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Support\Facades\Redis;
use Encore\Admin\Layout\Content;
//use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use App\Model\UserModel;


class GroupController extends Controller
{
    use HasResourceActions;

    public function index(Content $content)
    {

        $accessToken=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/get?access_token=$accessToken&next_openid=";
        $onjJson=file_get_contents($url);
        $info=json_decode($onjJson,true);
        $openid=$info['data']['openid'];
        $dataInfo=[];
        foreach ($openid as $k=>$v){
            $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$accessToken&openid=$v&lang=zh_CN";
            $response=file_get_contents($url);
            $data=json_decode($response,JSON_UNESCAPED_UNICODE);
            $dataInfo[]=$data;
        }
//        print_r($dataInfo);exit;

        //获取标签
        $labelUrl="https://api.weixin.qq.com/cgi-bin/tags/get?access_token=$accessToken";
        $labelJson=file_get_contents($labelUrl);
        $data=json_decode($labelJson,true);
        $labelInfo=$data['tags'];


        return $content
            ->header('Index')
            ->description('description')
            ->body(view('admin.weixin.imglist',['dataInfo'=>$dataInfo,'labelInfo'=>$labelInfo]));
    }

    public function openId(Request $request){
        $openid=$request->input('openid');
        $content=$request->input('content');
        $accessToken=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$accessToken";
        $arr=array(
            "touser"=>array(
                $openid
            ),
            "msgtype"=>"text",
            "text"=> array(
                "content"=>$content
            )
        );
        $strJson=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $client=new Client();
        $response=$client->request('POST',$url,[
           'body'=>$strJson
        ]);
        $objJson=$response->getBody();
        $info=json_decode($objJson,true);
        if ($info){
            $aar=array(
                'status'=>1,
                'msg'=>'发送成功'
            );
            return $aar;
        }else{
            $aar=array(
                'status'=>0,
                'msg'=>'发送失败'
            );
            return $aar;

        }

    }

    /**获取accessToken*/
    public function accessToken(){
        $key='aa';
        $token=Redis::get($key);
        if($token){

        }else{
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID_0').'&secret='.env('WEIXIN_MCH_KEY').'";
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
            print_r($token);
        }
        return $token;



    }


    /**给用户打上标签*/
    public function userLabel(){

    }


}
