<?php

namespace App\Http\Controllers\Weixin;

use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class WxjsController extends Controller
{
    protected $redis_weixin_access_token = 'str:weixin_access_token_jssdk';
    protected $redis_weixin_ticket = 'str:weixin_ticket_jssdk';
    public function test(){
        //计算签名

        $data=[
            'appid'=>env('WEIXIN_APPID_1'),
            'timestamp'=>time(),
            'nonceStr' => str_random(10), // 必填，生成签名的随机串
        ];
        $sign=$this->getSign($data);
        $data['sign'] = $sign;
        $info=[
            'data'=>$data
        ];
        return view('weixin.jssdk',$info);
    }

    /**
     * 计算签名
     * @param $info
     * @return string
     */
    public function getSign($info){
        //获取微信access_token
        $access_token=$this->getWXAccessToken();
        //获取ticket
        $ticket=$this->getTicket($access_token);
        $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        //对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后，使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1
        $str =  'jsapi_ticket='.$ticket.'&noncestr='.$info['nonceStr']. '&timestamp='. $info['timestamp']. '&url='.$current_url;

        //对$str进行sha1签名，得到signature：
        $sign=sha1($str);
        return $sign;
    }

    /**
     * 获取ticket
     * @param $access_token
     * @return mixed
     */
    public function getTicket($access_token){
        //获取缓存
        $ticket = Redis::get($this->redis_weixin_ticket);
        if(!$ticket){        // 无缓存 请求微信接口
            $url='https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
            $data = json_decode(file_get_contents($url),true);
            //记录缓存
            $ticket = $data['ticket'];
            Redis::set($this->redis_weixin_ticket,$ticket);
            Redis::setTimeout($this->redis_weixin_ticket,3600);
        }
        return $ticket;
    }
    /**
     * 获取微信AccessToken
     */
    public function getWXAccessToken()
    {

        //获取缓存
        $token = Redis::get($this->redis_weixin_access_token);
        if(!$token){        // 无缓存 请求微信接口
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WEIXIN_APPID_1').'&secret='.env('WEIXIN_APPSECRET_JSSDK');
            $data = json_decode(file_get_contents($url),true);
            //记录缓存
            $token = $data['access_token'];
            Redis::set($this->redis_weixin_access_token,$token);
            Redis::setTimeout($this->redis_weixin_access_token,3600);
        }
        return $token;

    }




    public function test1(){
        //echo 111;
       echo $_POST['html'];
    }
}
