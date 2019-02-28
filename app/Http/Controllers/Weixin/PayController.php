<?php

namespace App\Http\Controllers\Weixin;

use App\Model\DetailModel;
use App\Model\OrderModel;
use App\Model\UserModel;
use App\Model\WxuserModel;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PayController extends Controller
{
    public $weixin_unifiedorder_url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';

    public $weixin_notify_url = 'https://king.tactshan.com/weixin/pay/notice';     //支付通知回调

    public function test($order_num){
        //订单号
        $order_num='wh'.$order_num;
        //支付金额
        $total=1;
        $order_info = [
            'appid'         =>  env('WEIXIN_APPID_0'),      //微信支付绑定的服务号的APPID
            'mch_id'        =>  env('WEIXIN_MCH_ID'),       // 商户ID
            'nonce_str'     => str_random(16),             // 随机字符串
            'sign_type'     => 'MD5',
            'body'          => '订单号：'.$order_num,
            'out_trade_no'  => $order_num,                       //本地订单号
            'total_fee'     => $total,                          //支付金额
            'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],     //客户端IP
            'notify_url'    => $this->weixin_notify_url,        //通知回调地址
            'trade_type'    => 'NATIVE'                         // 交易类型
        ];


        $this->values=[];
        $this->values=$order_info;
        $sign=$this->SetSign();

        //将数组转化为xml
        $xml=$this->toXml();
        $res=$this->postXmlCurl($xml,$this->weixin_unifiedorder_url,$useCert=false,$second=30);
        $data =  simplexml_load_string($res);

        $code_url=$data->code_url;
        //将code_url传给前端控制器生成二维码
        return view('weixin.qrcode',['code_url'=>$code_url,'order_num'=>$order_num]);

    }

    public function postXmlCurl($xml,$url,$useCert=false,$second=30)
    {
        $ch=curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die("curl出错，错误码:$error");
        }
    }
    /**
     * @return string
     */
    protected function toXml(){
        if(!is_array($this->values)||count($this->values)<=0){
            exit('数组数据异常');
        }

        $xml="<xml>";
        foreach ($this->values as $k=>$v){
            if(is_numeric($v)){
                $xml.="<".$k.">".$v."</".$k.">";
            }else{
                $xml.="<".$k."><![CDATA[".$v."]]></".$k.">";
            }

        }
        $xml.="</xml>";
        return $xml;
    }

    public function SetSign()
    {
        //生成签名
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }

    /**
     * 生成签名
     */
    private function MakeSign()
    {
        //签名步骤一：按字典序排序参数
        ksort($this->values);
        $string = $this->ToUrlParams();
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".env('WEIXIN_MCH_KEY');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    protected function ToUrlParams()
    {
        $buff = "";
        foreach ($this->values as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }

    public function notice()
    {
        $data = file_get_contents("php://input");

        //记录日志
        $log_str = date('Y-m-d H:i:s') . "\n" . $data . "\n<<<<<<<";
        file_put_contents('logs/wx_pay_notice.log',$log_str,FILE_APPEND);

        $xml = (array)simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);

        if($xml['result_code']=='SUCCESS' && $xml['return_code']=='SUCCESS'){      //微信支付成功回调
            //验证订单金额
            if($xml['total_fee']!='1'){
                exit('订单金额有误');
            }
           //验证签名
            $this->values=[];
            $this->values=$xml;
            $sign=$this->SetSign();
            if($sign=$xml['sign']){       //签名验证成功
                //TODO 逻辑处理  订单状态更新
                $where=[
                    'order_num'=>substr($xml['out_trade_no'],2),
                ];
                //修改订单详情表
                $detailInfo=DetailModel::where($where)->get();
                if(empty($detailInfo)){
                    $log_str .= " Detail Failed!<<<<< \n\n";
                    file_put_contents('logs/Wxpay.log',$log_str,FILE_APPEND);
                }
                foreach ($detailInfo as $k=>$v){
                    $detail=[
                        'status'=>2
                    ];
                    DetailModel::where($where)->update($detail);
                }
                //修改订单状态
                //修改订单时间
                $orderData=[
                    'order_status'=>2,
                    'pay_time'=>time()
                ];
                $res=OrderModel::where($where)->update($orderData);
                if(!$res){
                    $log_str .= " Detail Failed!<<<<< \n\n";
                    file_put_contents('logs/Wxpay.log',$log_str,FILE_APPEND);
                }
            }else{
                //TODO 验签失败
                echo '验签失败，IP: '.$_SERVER['REMOTE_ADDR'];
                // TODO 记录日志
                $log_str .= " Sign Failed!<<<<< \n\n";
                file_put_contents('logs/Wxpay.log',$log_str,FILE_APPEND);
            }

        }

        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $response;

    }

    public function find(Request $request){
        $order_num=$request->input('order_num');
        $order_num=substr($order_num,2);
        $where=[
            'order_num'=>$order_num
        ];
        $orderInfo=OrderModel::where($where)->first();
        if($orderInfo->order_status=='2'){
            $data=[
                'code'=>1
            ];
            echo json_encode($data);
        }else{
            $data=[
                'code'=>2
            ];
            echo json_encode($data);
        }
    }

    public function getCode(Request $request){
        $code=$_GET['code'];
        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wxe24f70961302b5a5&secret=0f121743ff20a3a454e4a12aeecef4be&code='.$code.'&grant_type=authorization_code';
        $token_json = file_get_contents($token_url);

        $token_arr = json_decode($token_json,true);

        $access_token = $token_arr['access_token'];
        $openid = $token_arr['openid'];

        // 3 携带token  获取用户信息
        $user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $user_json = file_get_contents($user_info_url);

        $user_arr = json_decode($user_json,true);
        $where=[
            'unionid'=>$user_arr['unionid']
        ];
        $userInfo=WxuserModel::where($where)->first();
        if(empty($userInfo)){
            //添加入库
            //添加users表
            $user_data=[
                'name'=>'wx_'.str_random(5)
            ];
            $uid=UserModel::insertGetId($user_data);
            //添加wx_user表
            $info=[
                'openid'=>$user_arr['openid'],
                'nickname'=>$user_arr['nickname'],
                'sex'=>$user_arr['sex'],
                'headimgurl'=>$user_arr['headimgurl'],
                'subscribe_time'=>time(),
                'add_time'=>time(),
                'unionid'=>$user_arr['unionid'],
                'uid'=>$uid
            ];
            $id=WxuserModel::insertGetId($info);
            setcookie('uid',$uid,time()+60*60*24,'/','',false,true);
            setcookie('nickname',$user_arr['nickname'],time()+60*60*24,'/','',false,true);
            echo "<h1 font-color='red'>首次登录</h1><a href='/goods'>进入商品页面</a>";
        }else{
            //登录逻辑
            setcookie('uid',$userInfo['uid'],time()+60*60*24,'/','',false,true);
            setcookie('nickname',$userInfo['nickname'],time()+60*60*24,'/','',false,true);
            echo "<h1 font-color='red'>欢迎回来<h1><a href='/goods'>进入商品页面</a>";
        }
    }
}
