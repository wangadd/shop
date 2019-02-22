<?php

namespace App\Admin\Controllers;

use App\Model\WxPmMedia;
use App\Http\Controllers\Controller;
use App\Model\WxuserModel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use GuzzleHttp;
use Illuminate\Support\Facades\Redis;

class WxPmMediaController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';
    /**
     * 获取群发内容
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function GroupSendingView(Content $content){

        return $content
            ->header('群发消息')
            ->description('description')
            ->body($this->form1());
    }
    /**
     * 群发视图层
     * @return Form
     */
    public function form1(){
        $form=new Form(new WxPmMedia);
        $form->textarea('group','群发内容');
        return $form;
    }
    /**
    * 群发消息
    * @throws GuzzleHttp\Exception\GuzzleException
     */
    public function GroupSending(Request $request){
        $group=$request->input('group');
        //获取微信access_token
        $access_token=$this->getWXAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=".$access_token;
        //请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $userInfo=WxuserModel::all()->toArray();
        $arr=[];
        foreach ($userInfo as $k=>$v){
            $arr[]=$v['openid'];
        }
        $data=[
            "touser"=>$arr,
            "msgtype"=> "text",
            "text"=>["content"=>$group]
        ];
        $r=$client->request('POST',$url,[
            'body'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        //解析微信接口返回信息
        $request_arr=json_decode($r->getBody(),true);
        if($request_arr['errcode']==0){
            echo  'success';
        }else{
            echo "群发失败,错误代码".$request_arr['errcode'].",错误信息".$request_arr['errmsg'];
        }

    }
    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {

        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID').'&secret='.env('WEIXIN_APPSECRET');
            $data = json_decode(file_get_contents($url),true);

            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;

    }
    /**
     * 新增素材
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('新增素材')
            ->description('description')
            ->body($this->form());
    }
    protected function form()
    {
        $form = new Form(new WxPmMedia);

        $form->radio('type')->options(['image' => 'image','voice'=>'voice']);
        $form->image('media');

        return $form;
    }
    /**
     * 新增永久图片素材
     *
     */
    public function doCreate(Request $request){
        $type=$_POST['type'];
        $file=$request->file('media');
        $ext=$file->getClientOriginalExtension();
        $file_name=time().rand(10000,99999).'.'.$ext;
        $save_file_path=$request->media->storeAs('image',$file_name);
        //上传至永久素材
        $info=$this->upMedia($save_file_path,$type);
        $data=[
            'media_id'=>$info['media_id'],
            'url'=>$info['url'],
            'add_time'=>time()
        ];
        $rs=WxPmMedia::insertGetId($data);
        if($rs){
            echo "上传成功";
        }else{
            echo "上传失败";
        }
    }
    /**
     *  上传素材
     *  @param $file_path
     */
    public function upMedia($file_path,$type){
        //获取access_token
        $access_token=$this->getWXAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$access_token.'&type='.$type;
        //调用微信接口
        $client = new GuzzleHttp\Client();
        $res=$client->request('POST',$url,[
            'multipart' =>[
                [
                    'name'=>'media',
                    'contents'=>fopen($file_path,'r')
                ],
            ],
        ]);
        $body=$res->getBody();
        $result=json_decode($body,true);
        return $result;
    }
    /**
     * 素材展示
     * @param Content $content
     * @return Content
     */
    public function index(Content $content){
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }
    protected function grid()
    {
        $grid = new Grid(new WxPmMedia);

        $grid->id('Id');
        $grid->media_id('Media Id');
        $grid->url('Url')->display(function ($path){
            return "<img src='".$path."' style='width:100px; height:50px;'>";
        });
        $grid->add_time('Add time');
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            // 获取当前行主键值
            $id = $actions->getKey();
            //添加删除按钮
            $actions->append("<a href='/admin/auth/wxpmmedia/delete/".$id."'>删除</a>");
        });
        return $grid;
    }
    /**
     * 获取永久素材列表
     */
    public function getPmMedia(){
        //获取access_token
        $access_token=$this->getWXAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=".$access_token;
        //调用微信接口
        $client=new GuzzleHttp\Client(['base_uri' => $url]);
        $data=[
            "type"=>'image',
            "offset"=>0,
            "count"=>10,
        ];
        $r=$client->request('POST',$url,[
            'body'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);

        //解析微信接口返回信息
        $request_arr=json_decode($r->getBody(),true);
        var_dump($request_arr);die;
    }

    public function delete($id){
        $info=WxPmMedia::where(['id'=>$id])->first();
        $media_id=$info->media_id;
        //获取access_token
        $access_token=$this->getWXAccessToken();
        $url='https://api.weixin.qq.com/cgi-bin/material/del_material?access_token='.$access_token;
        //调用微信接口
        $client = new GuzzleHttp\Client();
        $data=[
            'media_id'=>$media_id,
        ];
        $r=$client->request('POST',$url,[
            'body'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        //解析微信接口返回信息
        $request_arr=json_decode($r->getBody(),true);
        $rs=WxPmMedia::where(['id'=>$id])->delete();
        if($request_arr['errcode']==0){
            echo "删除成功";
        }
    }
}
