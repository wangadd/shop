<?php

namespace App\Http\Controllers\Weixin;

use App\Model\WxuserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Redis;

class WxController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token1';
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
        //解析XML
        $xml = simplexml_load_string($data);        //将 xml字符串 转换成对象
        $event = $xml->Event;                       //事件类型

        if($event=='subscribe'){
            $openid = $xml->FromUserName;               //用户openid
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
        }else{
            $openid = $xml->FromUserName;
            $u = WxuserModel::where(['openid'=>$openid])->delete();
            if($u){
                echo "ok";
            }else{
                echo "fail";
            }
        }
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_event.log',$log_str,FILE_APPEND);
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
}