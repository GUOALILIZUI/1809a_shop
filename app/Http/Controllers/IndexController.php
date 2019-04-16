<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    /**测试*/
    public function Info(){
        phpinfo();
    }

    //接口配置
    public function check(Request $request){
        //首次接入检测请求是否为微信
        //echo $request->input('echostr');exit;

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
        $Content=$xmlObj->Content;
        $MediaId=$xmlObj->MediaId;

        //用户信息
        $access=$this->accessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access&openid=$FromUserName&lang=zh_CN";
        $response=file_get_contents($url);
        $info=json_decode($response,true);
        $name=$info['nickname'];

        //提示语
        if($Event=='subscribe'){
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
        $client=new Client;
        //素材
        if($MsgType=='text'){
            $cityName=explode('+',$Content)[0];
            //获取天气
            $weaUrl="https://free-api.heweather.net/s6/weather/now?location=$cityName&key=HE1904161102191951";
            $weaStr=file_get_contents($weaUrl);
            $weaInfo=json_decode($weaStr,true);
            //print_r($weaInfo);exit;
            $city=$weaInfo['HeWeather6'][0]['basic']['location']; //城市
            $wind_dir=$weaInfo['HeWeather6'][0]['now']['wind_dir']; //风力
            $wind_sc=$weaInfo['HeWeather6'][0]['now']['wind_sc']; //风向
            $tmp=$weaInfo['HeWeather6'][0]['now']['tmp']; //温度
            $status=$weaInfo['HeWeather6'][0]['status']; //状态

            //回复消息
            if($status=='ok'){
                $text='城市：'.$city."\n".'风力：'.$wind_sc."\n".'风向：'.$wind_dir."\n".'温度：'.$tmp."\n";
                $time=time();
                $xmlStr="
                   <xml>
                        <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                        <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                        <CreateTime>$time</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[$text]]></Content>
                   </xml>";
                echo $xmlStr;
            }else{
                $text="你的城市名是乱写的！！！";
                $time=time();
                $xmlStr="
                   <xml>
                        <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                        <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                        <CreateTime>$time</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA[$text]]></Content>
                   </xml>";
                echo $xmlStr;
            }


            //如果文本就入库
                $TextData=[
                    'nickname'=>$name,
                    'text'=>$Content,
                    'sex'=>$info['sex'],
                    'openid'=>$FromUserName,
                    'time'=>$CreateTime,
                ];
                DB::table('xu')->insert($TextData);
        }else if($MsgType=='voice'){
            //语音下载
            $voUrl="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$MediaId";
            $voStr=$client->get(new Uri($voUrl));
            //获取响应头信息
            $headers=$voStr->getHeaders();
            //获取文件名
            $voFileInfo=$headers['Content-disposition'][0];
            $voFileName=rtrim(substr($voFileInfo,-20),'""');
            $newVoFileName=date('Y-m-d H:i:s').$voFileName;
            ////内容写入磁盘文件 默认写入storage/app/
            $res1=Storage::put($newVoFileName,$voStr->getBody());
            if($res1=='1'){
                //echo 11;
                $voiceData=[
                    'nickname'=>$name,
                    'openid'=>$FromUserName,
                    'voice'=>$newVoFileName
                ];
                DB::table('voice')->insert($voiceData);
            }
        }else if($MsgType=='image'){
            //图片下载
            $imgUrl="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$MediaId";
            $imgStr=$client->get(new Uri($imgUrl));
            //获取响应头信息
            $headers=$imgStr->getHeaders();
            //获取文件名
            $fileInfo=$headers['Content-disposition'][0];
            $fileName=rtrim(substr($fileInfo,-20),'""');
            $newFileName='/wwwroot/1809a_shop/img/'.date('Y-m-d H:i:s').$fileName;
            //内容写入磁盘文件 默认写入storage/app/
            $res2=Storage::put($newFileName,$imgStr->getBody());
            if($res2=='1'){
                //echo 11;exit;
                $imgData=[
                    'nickname'=>$name,
                    'openid'=>$FromUserName,
                    'img'=>$newFileName
                ];
                DB::table('image')->insert($imgData);
            }
        }

    }

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

    /**一级菜单*/
    public function custom()
    {
        $access = $this->accessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access";
        $arr = array(
            "button"=>array(
                array(
                    "type"=>"click",
                    "name"=>"酸奶",
                    "key"=>"V1001_TODAY_MUSIC"
                ),
                array(
                    "name"=>"酸酸的",
                    "sub_button"=>array(
                        array(
                            "type"=>"view",
                            "name"=>"百度度",
                            "url"=>"http://www.baidu.com/"
                        )

                    ),
                )

            )
        );
        $strJson=json_encode($arr,JSON_UNESCAPED_UNICODE);
        $client=new Client();
        $response =$client->request('POST',$url,[
            'body'  => $strJson,
        ]);
        $objJson=$response->getBody();
        $info=json_decode($objJson,true);
        print_r($info);

    }

}
