<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{	
    public function Info(){
	phpinfo();
    }
				
   //接口配置
    public function check(Request $request){
	    //首次接入检测请求是否为微信
	    echo $request->input('echostr');
	    $content=file_get_contents("php://input");
	    $time=date('Y-m-d H:i:s');
	    $str=$time.$content."\n";
	    file_put_contents("logs/check.log",$str,FILE_APPEND);
	    echo 'success';
    }
	
	
}
