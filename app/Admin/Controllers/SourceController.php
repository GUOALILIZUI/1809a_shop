<?php

namespace App\Admin\Controllers;

use App\Model\SourceModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use GuzzleHttp\Client;

class SourceController extends Controller
{
    use HasResourceActions;

    public function index(Content $content)
    {


        return $content
            ->header('Index')
            ->description('description')
            ->body(view('admin.weixin.imgadd'));
    }

    public function upload(Content $content){
        $file=$_FILES;
        $name=$file['img']['name'];
        $tmp_name=$file['img']['tmp_name'];
        $newName=rand(11111,99999)."$name";
        $path="/wwwroot/1809a_shop/public/source/$newName";
        move_uploaded_file($tmp_name,$path);
        //print_r($name);
        if($file){
            $accessToken=$this->accessToken();
            $url="https://api.weixin.qq.com/cgi-bin/media/upload?access_token=$accessToken&type=image";
            $client=new Client();
            $response=$client->request('post',$url,[
                'multipart' => [
                    [
                        'name'     => 'media',
                        'contents' => fopen("source/$newName", "r+")
                    ]
                ]
            ]);
            $objJson= $response->getBody();
            $info=json_decode($objJson,true);
            $data=[
                'type'=>$info['type'],
                'media_id'=>$info['media_id'],
                'created_at'=>$info['created_at'],
            ];
            DB::table('source')->insert($data);
            die('上传成功');
        }else{
            return $content
                ->header('Index')
                ->description('description')
                ->body(view('admin.weixin.imgadd'));
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

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SourceModel);



        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(SourceModel::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SourceModel);



        return $form;
    }
}
