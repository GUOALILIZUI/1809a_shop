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
 	   echo $request->input('echostr');
    }
	
	
}
