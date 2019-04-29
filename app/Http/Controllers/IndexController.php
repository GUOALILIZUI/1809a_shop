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
//        echo $request->input('echostr');exit;
        $content=file_get_contents("php://input");
        $time=date("Y-m-d H:i:s");
        $str=$time.$content."\n";
        file_put_contents("logs/check.log",$str,FILE_APPEND);
        $xmlObj=simplexml_load_string($content);
//        var_dump($xmlObj);exit;

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

        }else if($Event=='SCAN'){
            $userData=[
                'nickname'=>$info['nickname'],
                'sex'=>$info['sex'],
                'img'=>$info['headimgurl'],
                'openid'=>$info['openid']
            ];
            DB::table('tmp_wx_user')->insert($userData);
            $time=time();
            $title=$name."扫码成功啦";
            $description="喵喵喵";
            $picurl="http://img4.imgtn.bdimg.com/it/u=4043356389,1557346799&fm=26&gp=0.jpg";
            $url1="https://1809guomingyang.comcto.com/code";
            $xmlStr="<xml>
                      <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                      <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                      <CreateTime>$time</CreateTime>
                      <MsgType><![CDATA[news]]></MsgType>
                      <ArticleCount>1</ArticleCount>
                      <Articles>
                        <item>
                          <Title><![CDATA[$title]]></Title>
                          <Description><![CDATA[$description]]></Description>
                          <PicUrl><![CDATA[$picurl]]></PicUrl>
                          <Url><![CDATA[$url1]]></Url>
                        </item>
                      </Articles>
                   </xml>";
            echo $xmlStr;
        }
        $client=new Client;
        //素材
        if($MsgType=='voice'){
            //语音下载
            $voUrl="https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$MediaId";
            $voStr=$client->get(new Uri($voUrl));
            //获取响应头信息
            $headers=$voStr->getHeaders();
            //获取文件名
            $voFileInfo=$headers['Content-disposition'][0];
            $voFileName=rtrim(substr($voFileInfo,-20),'""');
            $newVoFileName='/storage/app/'.date('Y-m-d H:i:s').$voFileName;
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
            $newFileName='/storage/app/'.date('Y-m-d H:i:s').$fileName;
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
        }else if($MsgType=='text'){
            $goodsInfo=DB::table('shop_goods')->where('goods_name','like',"%$Content%")->first();
//            print_r($goodsInfo);exit;
            if($goodsInfo){
                $goods_name=$goodsInfo->goods_name;
                $pice=$goodsInfo->goods_selfprice;
                $title=$goods_name.'  ￥：'.$pice;
                $description='全天下最好的商品';
                $picurl='https://1809guomingyang.comcto.com/goodsimg/'.$goodsInfo->goods_img;
                $url1="https://1809guomingyang.comcto.com";
                $xmlStr="<xml>
                      <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                      <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                      <CreateTime>$time</CreateTime>
                      <MsgType><![CDATA[news]]></MsgType>
                      <ArticleCount>1</ArticleCount>
                      <Articles>
                        <item>
                          <Title><![CDATA[$title]]></Title>
                          <Description><![CDATA[$description]]></Description>
                          <PicUrl><![CDATA[$picurl]]></PicUrl>
                          <Url><![CDATA[$url1]]></Url>
                        </item>
                      </Articles>
                   </xml>";
                echo $xmlStr;
            }else{
                $goodsInfo2=DB::table('shop_goods')->orderBy('goods_salenum','asc')->first();
                $goods_name2=$goodsInfo2->goods_name;
                $pice2=$goodsInfo2->goods_selfprice;
                $title=$goods_name2.'  ￥：'.$pice2;
                $description='全天下最好的商品';
                $picurl='https://1809guomingyang.comcto.com/goodsimg/'.$goodsInfo->goods_img;
                $url1='https://1809guomingyang.comcto.com';
                $xmlStr="<xml>
                      <ToUserName><![CDATA[$FromUserName]]></ToUserName>
                      <FromUserName><![CDATA[$ToUserName]]></FromUserName>
                      <CreateTime>$time</CreateTime>
                      <MsgType><![CDATA[news]]></MsgType>
                      <ArticleCount>1</ArticleCount>
                      <Articles>
                        <item>
                          <Title><![CDATA[$title]]></Title>
                          <Description><![CDATA[$description]]></Description>
                          <PicUrl><![CDATA[$picurl]]></PicUrl>
                          <Url><![CDATA[$url1]]></Url>
                        </item>
                      </Articles>
                   </xml>";
                echo $xmlStr;
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
            $response=file_get_contents($url);
            //echo $response;echo '<hr>';exit;
            $arr=json_decode($response,true);

            //accesstoken存缓存
            $key='aa';
            Redis::set($key,$arr['access_token']);
            //Redis::get($key);
            Redis::expire($key,7200);
            $token=$arr['access_token'];
            print_r($token);
        }
        return $token;



    }


}
