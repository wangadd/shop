<?php

namespace App\Admin\Controllers;

use App\Model\WxuserModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Request;
use GuzzleHttp;

class WeixinController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
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
        $grid = new Grid(new WxuserModel);

        $grid->id('Id');
        $grid->uid('Uid');
        $grid->openid('Openid');
        $grid->add_time('Add time');
        $grid->nickname('Nickname');
        $grid->sex('Sex');
	    $grid->headimgurl('Headimgurl')->display(function($img_url){
		    return "<img src='".$img_url."' style='width: 50px; height:50px;'></img>";
	    });
        $grid->subscribe_time('Subscribe time');
        $grid->actions(function ($actions) {
            // 获取当前行主键值
            $id = $actions->getKey();
            //添加互动按钮
            $actions->append("<a href='/admin/auth/wxuser/send/".$id."'>互动</a>");
        });
        return $grid;
    }
    public function sendView(Content $content,$id){
        $where=[
            'id'=>$id
        ];
        $info=WxuserModel::where($where)->first();
        $openid=$info->openid;
        return $content
            ->header('互动消息')
            ->description('description')
            ->body($this->form1($openid));
    }
    public function form1($openid){
        $form = new Form(new WxuserModel);
        $form->hidden('openid', 'Openid')->value($openid);
        $form->text('text', 'text');
        return $form;
    }
    /**
     * 给用户发送消息
     *
     */
    public function send(Request $request){
        $openid=$_POST['openid'];
        $text=$_POST['text'];
        $access_token=$this->getWXAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=".$access_token;
        //调用微信接口
        $client = new GuzzleHttp\Client();
        $data=[
            "touser"=>$openid,
            "msgtype"=>"text",
            "text"=>[
                "content"=>$text
            ],
        ];
        $r=$client->request('POST',$url,[
            'body'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);
        //解析微信接口返回信息
        $request_arr=json_decode($r->getBody(),true);
        if($request_arr['errcode']==0){
            echo "发送成功";
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
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(WxuserModel::findOrFail($id));

        $show->id('Id');
        $show->uid('Uid');
        $show->openid('Openid');
        $show->add_time('Add time');
        $show->nickname('Nickname');
        $show->sex('Sex');
        $show->headimgurl('Headimgurl');
        $show->subscribe_time('Subscribe time');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WxuserModel);

        $form->number('uid', 'Uid');
        $form->text('openid', 'Openid');
        $form->number('add_time', 'Add time');
        $form->text('nickname', 'Nickname');
        $form->switch('sex', 'Sex');
        $form->text('headimgurl', 'Headimgurl');
        $form->number('subscribe_time', 'Subscribe time');

        return $form;
    }
}
