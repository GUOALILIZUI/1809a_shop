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


class TagController extends Controller
{
    use HasResourceActions;

    public function index(Content $content)
    {
        $accessToken=$this->accessToken();
        $labelUrl="https://api.weixin.qq.com/cgi-bin/tags/get?access_token=$accessToken";
        $labelJson=file_get_contents($labelUrl);
        $data=json_decode($labelJson,true);
        $labelInfo=$data['tags'];

        return $content
            ->header('Index')
            ->description('description')
            ->body(view('admin.weixin.tag',['labelInfo'=>$labelInfo]));
    }

    /**标签群发*/
    public function tag(Request $request){
        $label=$request->input('sel');
        $content=$request->input('content');
        $accessToken=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=$accessToken";
        $arr=array(
                "filter"=>array(
                "is_to_all"=>false,
                "tag_id"=>$label
            ),
           "text"=>array(
                "content"=>$content
           ),
            "msgtype"=>"text"
        );
        $str=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $client=new Client();
        $response=$client->request('POST',$url,[
            'body'=>$str
        ]);
        $obj=$response->getBody();
        $info=json_decode($obj,true);


        $id=Redis::incr('id');
        $hkey='hks'.$id;
        Redis::hset($hkey,'id',$id);
        Redis::hset($hkey,'content',$content);

        $lkey='lks';
        Redis::lpush($lkey,$hkey);

//        print_r($info);exit;
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


}
