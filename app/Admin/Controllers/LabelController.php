<?php

namespace App\Admin\Controllers;

use App\Model\SourceModel;
use DemeterChain\C;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Support\Facades\Redis;
use Encore\Admin\Layout\Content;
//use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Client;
use App\Model\UserModel;


class LabelController extends Controller
{
    use HasResourceActions;

    /**标签添加视图*/
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body(view('admin.weixin.labeladd'));
    }

    /**标签添加数据*/
    public function LabelAdd(Request $request){
        $accessToken=$this->accessToken();
        $content=$request->input('content');
        $url="https://api.weixin.qq.com/cgi-bin/tags/create?access_token=$accessToken";
        $arr=array(
              "tag" =>array(
                  "name" =>$content
              )
        );
        $str=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $client=new Client();
        $response=$client->request('POST',$url,[
           'body'=>$str
        ]);
        $obj=$response->getBody();
        $info=json_encode($obj,true);
        if ($info){
            $aar=array(
                'status'=>1,
                'msg'=>'添加成功'
            );
            return $aar;
        }else{
            $aar=array(
                'status'=>0,
                'msg'=>'添加失败'
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

    /**标签展示*/
    public function LabelList(Content $content){
        $accessToken=$this->accessToken();
        $lurl="https://api.weixin.qq.com/cgi-bin/tags/get?access_token=$accessToken";
        $labelJson=file_get_contents($lurl);
        $info=json_decode($labelJson,true);
        $labelInfo=$info['tags'];

        return $content
            ->header('Index')
            ->description('description')
            ->body(view('admin.weixin.labellist',['labelInfo'=>$labelInfo]));


        
    }

    public function lableDo(Request $request){

    }


}
