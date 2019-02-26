<?php

namespace App\Http\Controllers\Weixin;

use App\Model\DetailModel;
use App\Model\OrderModel;
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
        $sign=$this->SetSign($this->values);

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

    public function SetSign($order_info)
    {
        //生成签名
        $sign = $this->MakeSign($order_info);
        $this->values['sign'] = $sign;
        return $sign;
    }

    /**
     * 生成签名
     */
    private function MakeSign($order_info)
    {
        //签名步骤一：按字典序排序参数
        ksort($order_info);
        $string = $this->ToUrlParams($order_info);
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
    protected function ToUrlParams($order_info)
    {
        $buff = "";
        foreach ($order_info as $k => $v)
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

        $xml = simplexml_load_string($data);
        if($xml->result_code=='SUCCESS' && $xml->return_code=='SUCCESS'){      //微信支付成功回调
            //验证订单金额
            if($xml->total_fee!='1'){
                exit('订单金额有误');
            }
//            //验证签名
//            $order_info = [
//                'appid'         =>  $xml->appid,      //微信支付绑定的服务号的APPID
//                'mch_id'        =>  $xml->mch_id ,       // 商户ID
//                'nonce_str'     => $xml->nonce_str,             // 随机字符串
//                'sign_type'     => 'MD5',
//
//                'out_trade_no'  => $xml->out_trade_no,                       //本地订单号
//                'total_fee'     => $xml->total_fee,                          //支付金额
//                'spbill_create_ip'  => $_SERVER['REMOTE_ADDR'],     //客户端IP
//                'notify_url'    => $this->weixin_notify_url,        //通知回调地址
//                'trade_type'    => $xml->trade_type                         // 交易类型
//            ];

//            $sign=$this->SetSign($order_info);
            $sign=true;
            if($sign){       //签名验证成功
                //TODO 逻辑处理  订单状态更新
                $where=[
                    'order_num'=>$xml->out_trade_no,
                ];
                //修改订单详情表
                $detailInfo=DetailModel::where($where)->get();
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
            }else{
                //TODO 验签失败
                echo '验签失败，IP: '.$_SERVER['REMOTE_ADDR'];
                // TODO 记录日志
            }

        }

        $response = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        echo $response;

    }

    public function find(Request $request){
        $order_num=$request->input('order_num');
        $order_num=substr($order_num,1);
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

}
