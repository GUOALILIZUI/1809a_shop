<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\File;
use Illuminate\Support\Str;


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
                $url1='https://1809guomingyang.comcto.com/goodsimg/'.$goodsInfo->goods_img;
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
                $goodsInfo2=DB::table('shop_goods')->orderBy('goods_salenum','desc')->first();
                $goods_name2=$goodsInfo2->goods_name;
                $pice2=$goodsInfo2->goods_selfprice;
                $title=$goods_name2.'  ￥：'.$pice2;
                $description='全天下最好的商品';
                $picurl='https://1809guomingyang.comcto.com/goodsimg/'.$goodsInfo2->goods_img;
                $url1='https://1809guomingyang.comcto.com/goodsimg/'.$goodsInfo2->goods_img;
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
            Redis::expire($key,7600);
            $token=$arr['access_token'];
            print_r($token);
        }
        return $token;
    }

    /**一级菜单*/
    public function custom()
    {
        $access = $this->accessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access";
        $curl="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxdd0d451ebdddd4f9&redirect_uri=https://1809guomingyang.comcto.com/weshow&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        $qurl="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxdd0d451ebdddd4f9&redirect_uri=https://1809guomingyang.comcto.com/qshow&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
        $arr = array(
            "button"=>array(
                        array(
                            "type"=>"view",
                            "name"=>"最新福利",
                            "url"=>$curl
                        ),
                array(
                    "type"=>"view",
                    "name"=>"签到",
                    "url"=>$qurl
                ),
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
    /**微信授权*/
    public function we(Request $request){
        $appId="wxdd0d451ebdddd4f9";
        $secret="3a0980e46f62a1f9b759fa11adaab484";
        $redirect_uri='https://1809guomingyang.comcto.com/code';
       $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appId&redirect_uri=$redirect_uri&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
//        print_r($url);
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

    /**微信*/
    public function weshow(){
        $code=$_GET['code'];
        $appId="wxdd0d451ebdddd4f9";
        $secret="3a0980e46f62a1f9b759fa11adaab484";
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$secret&code=$code&grant_type=authorization_code";
        $info=file_get_contents($url);
        $info2=json_decode($info);
        $openID=$info2->openid;

        $access=$this->accessToken();
        $urll="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access&openid=$openID&lang=zh_CN";
        $objJson=file_get_contents($urll);
        $info3=json_decode($objJson,true);
        $nickname=$info3['nickname'];


        $UserInfo=DB::table('suser')->where('openid',$openID)->first();
        if($UserInfo){
            echo '欢迎'. $nickname.'回来，正在跳转至福利页面';
            header('Refresh:3;url=/cc');
        }else{
            $dd=[
                'nickname'=>$nickname,
                'sex'=>$info3['sex'],
                'img'=>$info3['headimgurl'],
                'openid'=>$info3['openid']
            ];
            DB::table('suser')->insert($dd);
            echo '欢迎'. $nickname.'正在跳转至福利页面';
            header('Refresh:3;url=/cc');
        }


    }

    /***微信*/
    public function cc(){
        $ticket1=$this->getJsapiTicket();
        $nonceStr = Str::random(10);
        $time = time();
        $current_url = "https" . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
        $string1 = "jsapi_ticket=$ticket1&noncestr=$nonceStr&timestamp=$time&we=$current_url";
        $sign = sha1($string1);
        $signInfo=[
            'appId'=>'wxdd0d451ebdddd4f9',
            'timestamp'=>$time,
            'nonceStr'=>$nonceStr,
            'signature'=>$sign,
        ];
        return view('we.cc',['signInfo'=>$signInfo]);
    }

    /**qianm授权*/
    public function qshow(Request $request){
        $code=$_GET['code'];
        $appId="wxdd0d451ebdddd4f9";
        $secret="3a0980e46f62a1f9b759fa11adaab484";
        $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appId&secret=$secret&code=$code&grant_type=authorization_code";
        $info=file_get_contents($url);
        $info2=json_decode($info);
        $openID=$info2->openid;

        $access=$this->accessToken();
        $urll="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access&openid=$openID&lang=zh_CN";
        $objJson=file_get_contents($urll);
        $info3=json_decode($objJson,true);
        $nickname=$info3['nickname'];
        $time=time();

        $id=Redis::incr('id');
        $hkey='hqd_'.$id;
        Redis::hset($hkey,'id',$id);
        Redis::hset($hkey,'nickname',$nickname);
        Redis::hset($hkey,'time',$time);

        $lkey='lqd';
        Redis::lpush($lkey,$hkey);

//        Redis::lrange($lkey,0,-1);

    }


}
