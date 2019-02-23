<?php

namespace App\Http\Controllers\Weixin;

use App\Model\WxMediaModel;
use App\Model\WxTextModel;
use App\Model\WxuserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp;
use Illuminate\Support\Facades\Storage;

class WxController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token';
    /**
     * 首次接入
     */
    public function valid()
    {
        echo $_GET['echostr'];
    }
    /**
     * 接收微信服务器事件推送
     */
    public function wxEvent()
    {

        $data = file_get_contents("php://input");
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
        //解析XML
        $xml = simplexml_load_string($data);        //将 xml字符串 转换成对象
        $event = $xml->Event;                       //事件类型
        $openid = $xml->FromUserName;               //用户openid

        //处理用户发送的文本消息
        if(isset($xml->MsgType)){
            if($xml->MsgType=='肯德基奥斯卡的话'){
//
            }elseif ($xml->MsgType=='image'){
                $MediaId=$xml->MediaId;
                //获取微信access_token
                $access_token=$this->getWXAccessToken();
                //保存文件
                $returnInfo=$this->baocunwenjian($access_token,$MediaId,1);

                $xml_response = '<xml>
                                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                                <FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName>
                                <CreateTime>'.time().'</CreateTime>
                                <MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA['. $returnInfo['url'] .']]></Content>
                                </xml>';
                $media_data=[
                    'openid'=>$openid,
                    'add_time'=>time(),
                    'msgtype'=>$xml->MsgType,
                    'msg_id'=>$xml->MsgId,
                    'mediaid'=>$xml->MediaId,
                    'format'=>$xml->Format,
                    'file_name'=>$returnInfo['file_name'],
                    'file_path'=>$returnInfo['wx_image_path']
                ];
                $res=WxMediaModel::insertGetId($media_data);
                echo $xml_response;
            }elseif ($xml->MsgType=='voice'){
                $MediaId=$xml->MediaId;
                //获取微信access_token
                $access_token=$this->getWXAccessToken();
                //获取文件名
                $returnInfo=$this->baocunwenjian($access_token,$MediaId,2);
                $xml_response = '<xml>
                                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                                <FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName>
                                <CreateTime>'.time().'</CreateTime>
                                <MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA['. $returnInfo['url'] .']]></Content>
                                </xml>';
                $media_data=[
                    'openid'=>$openid,
                    'add_time'=>time(),
                    'msgtype'=>$xml->MsgType,
                    'msg_id'=>$xml->MsgId,
                    'mediaid'=>$xml->MediaId,
                    'format'=>$xml->Format,
                    'file_name'=>$returnInfo['file_name'],
                    'file_path'=>$returnInfo['wx_image_path']
                ];
                $res=WxMediaModel::insertGetId($media_data);
                echo $xml_response;
            }elseif($xml->MsgType=='video'){
                $MediaId=$xml->MediaId;
                //获取微信access_token
                $access_token=$this->getWXAccessToken();
                //获取文件名
                $returnInfo=$this->baocunwenjian($access_token,$MediaId,3);
                $xml_response = '<xml>
                                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                                <FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName>
                                <CreateTime>'.time().'</CreateTime>
                                <MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA['.$returnInfo['url'] .']]></Content>
                                </xml>';
                $media_data=[
                    'openid'=>$openid,
                    'add_time'=>time(),
                    'msgtype'=>$xml->MsgType,
                    'msg_id'=>$xml->MsgId,
                    'mediaid'=>$xml->MediaId,
                    'format'=>$xml->Format,
                    'file_name'=>$returnInfo['file_name'],
                    'file_path'=>$returnInfo['wx_image_path']
                ];
                $res=WxMediaModel::insertGetId($media_data);
                echo $xml_response;
            }elseif($xml->MsgType=='music'){
                $MediaId=$xml->MediaId;
                //获取微信access_token
                $access_token=$this->getWXAccessToken();
                //获取文件名
                $returnInfo=$this->baocunwenjian($access_token,$MediaId,4);
                $xml_response = '<xml>
                                <ToUserName><![CDATA['.$openid.']]></ToUserName>
                                <FromUserName><![CDATA['.$xml->ToUserName.']]></FromUserName>
                                <CreateTime>'.time().'</CreateTime>
                                <MsgType><![CDATA[text]]></MsgType>
                                <Content><![CDATA['. $returnInfo['url'] .']]></Content>
                                </xml>';
                $media_data=[
                    'openid'=>$openid,
                    'add_time'=>time(),
                    'msgtype'=>$xml->MsgType,
                    'msg_id'=>$xml->MsgId,
                    'mediaid'=>$xml->MediaId,
                    'format'=>$xml->Format,
                    'file_name'=>$returnInfo['file_name'],
                    'file_path'=>$returnInfo['wx_image_path']
                ];
                $res=WxMediaModel::insertGetId($media_data);
                echo $xml_response;
            }else{
                if($event=='subscribe'){
                    $sub_time = $xml->CreateTime;               //扫码关注时间
                    //获取用户信息
                    $user_info = $this->getUserInfo($openid);
                    //保存用户信息
                    $u = WxuserModel::where(['openid'=>$openid])->first();
                    if($u){       //用户不存在
                        echo '用户已存在';
                    }else{
                        $user_data = [
                            'openid'            => $openid,
                            'add_time'          => time(),
                            'nickname'          => $user_info['nickname'],
                            'sex'               => $user_info['sex'],
                            'headimgurl'        => $user_info['headimgurl'],
                            'subscribe_time'    => $sub_time,
                        ];
                        $id = WxuserModel::insertGetId($user_data);      //保存用户信息
                        if($id){
                            echo "success";
                        }else{
                            echo "fail";
                        }
                    }
                }elseif ($event=='CLICK'){
                    if($xml->EventKey=='V1001_TODAY_MUSIC'){
                        $this->kefu01($openid,$xml->ToUserName);
                    }elseif ($xml->EventKey=='test'){
                        $this->kefu02($openid,$xml->ToUserName);
                    }
                }else{
                    $openid = $xml->FromUserName;
                    $u = WxuserModel::where(['openid'=>$openid])->delete();
                    if($u){
                        echo "ok";
                    }else{
                        echo "no";
                    }
                }
            }
        }
    }
    /**
     * 获取文件名称
     */
    public function baocunwenjian($access_token,$MediaId,$int){
        $url='https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$access_token.'&media_id='.$MediaId;

        //保存语音文件
        $client = new GuzzleHttp\Client();
        $response = $client->get($url);
        //获取文件名
        $file_info = $response->getHeader('Content-disposition');
        $file_name = substr(rtrim($file_info[0],'"'),-20);
        if($int==1){
            $wx_image_path = 'wx/images/'.$file_name;
        }elseif ($int==2){
            $wx_image_path = 'wx/voice/'.$file_name;
        }elseif ($int==3){
            $wx_image_path = 'wx/video/'.$file_name;
        }elseif ($int==4){
            $wx_image_path = 'wx/music/'.$file_name;
        }
        //保存素材
        $r = Storage::disk('local')->put($wx_image_path,$response->getBody());
        $data=[
            'file_name'=>$file_name,
            'url'=>$url,
            'wx_image_path'=>$wx_image_path
        ];
        return $data;
    }
    /**
     * 客服处理
     * @param $openid   用户openid
     * @param $from     开发者公众号id 非 APPID
     */
    public function kefu01($openid,$from)
    {
        // 文本消息
        $xml_response = '<xml>
                        <ToUserName><![CDATA['.$openid.']]></ToUserName>
                        <FromUserName><![CDATA['.$from.']]></FromUserName>
                        <CreateTime>'.time().'</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA['. '欢迎访问, 现在时间'. date('Y-m-d H:i:s') .']]></Content>
                        </xml>';
        echo $xml_response;
    }
    /**
     * 客服处理2
     * @param $openid   用户openid
     * @param $from     开发者公众号id 非 APPID
     */
    public function kefu02($openid,$from)
    {
        // 文本消息
        $xml_response = '<xml>
                        <ToUserName><![CDATA['.$openid.']]></ToUserName>
                        <FromUserName><![CDATA['.$from.']]></FromUserName>
                        <CreateTime>'.time().'</CreateTime>
                        <MsgType><![CDATA[text]]></MsgType>
                        <Content><![CDATA['. '开什么玩笑还想要图'.']]></Content>
                        </xml>';
        echo $xml_response;
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
     * 获取用户信息
     * @param $openid
     */
    public function getUserInfo($openid)
    {
        $access_token = $this->getWXAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $data = json_decode(file_get_contents($url),true);
        return $data;
    }
    /**
     * 创建菜单
     */
    public function createMenu(){
        //获取微信access_token
        $access_token=$this->getWXAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
        //请求微信接口
        $client = new GuzzleHttp\Client(['base_uri' => $url]);
        $data=[
            "button"=>[
                [
                    "type"=>"click",
                    "name"=>"点此自动回复",
                    "key"=>"V1001_TODAY_MUSIC"
                ],
                [
                    "type"=>"view",
                    "name"=>"进行群发",
                    "url"=>"https://king.tactshan.com/weixin/groupsending"
                ],
                [
                    "name"=> "发图",
                    "sub_button"=>[
                        [
                            "type"=> "pic_sysphoto",
                            "name"=> "系统拍照发图",
                            "key"=>"rselfmenu_1_0",
                        ],
                        [
                            "type"=> "pic_photo_or_album",
                            "name"=> "拍照或者相册发图",
                            "key"=> "rselfmenu_1_1",
                        ],
                    ],
                ],
            ],
        ];

        $r=$client->request('POST',$url,[
            'body'=>json_encode($data,JSON_UNESCAPED_UNICODE)
        ]);

        //解析微信接口返回信息
        $request_arr=json_decode($r->getBody(),true);
        if($request_arr['errcode']==0){
            echo "创建菜单成功";
        }else{
            echo "创建菜单失败,错误代码".$request_arr['errcode'];
        }

    }
    /**
     * 获取群发内容
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function GroupSendingView(){
        return view('weixin.groupsending');
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
            echo "群发成功";
        }else{
            echo "群发失败,错误代码".$request_arr['errcode'].",错误信息".$request_arr['errmsg'];
        }

    }
    /**
     * 试图
     */
    public function Wxuser(){
        $userInfo=WxuserModel::all();
        $info=[
            'data'=>$userInfo
        ];
        return view('weixin.userlist',$info);
    }
    public function sendView($id){
        $where=[
            'id'=>$id
        ];
        $info=WxuserModel::where($where)->first();
        $data=[
            'info'=>$info,
            'textInfo'=>[],
        ];
        return view('weixin.sendview',$data);
    }
    public function send(Request $request){
        $openid=$request->input('openid');
        $text=$request->input('text');
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
            $arr=[
                'code'=>1,
                'msg'=>'发送成功'
            ];
            $kefuInfo=[
                'openid'=>1,
                'text'=>$text,
                'senduser'=>'客服',
                'add_time'=>time()
            ];
            $rs=WxTextModel::insertGetId($kefuInfo);
            echo json_encode($arr);
        }else{
            echo "发送失败,错误代码".$request_arr['errcode'].",错误信息".$request_arr['errmsg'];
        }
    }
    /**
     * 获取回复消息
     */
    public function huifu(Request $request){
        //获取用户回复消息
        $data = file_get_contents("php://input");
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
        //解析XML
        $xml = simplexml_load_string($data);        //将 xml字符串 转换成对象
        $event = $xml->Event;                       //事件类型
        $openids = $xml->FromUserName;               //用户openid
        $msg=$xml->Content;
        $yhInfo=[
            'openid'=>$openids,
            'senduser'=>$openids,
            'text'=>$msg,
            'add_time'=>time(),
        ];
        $rs=WxTextModel::InsertGetId($yhInfo);


    }
    public function getMsg(Request $request){
        $openid=$request->input('openid');
        $msg=WxTextModel::orderBy('add_time','asc')->where('openid',$openid)->get();
        echo json_encode($msg);
    }
}
