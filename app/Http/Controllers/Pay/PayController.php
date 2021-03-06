<?php

namespace App\Http\Controllers\Pay;

use App\Model\DetailModel;
use App\Model\OrderModel;
use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class PayController extends Controller
{
    //

    public $app_id = '2016092200571887';
    public $gate_way = "http://openapi.alipaydev.com/gateway.do";
    public $notify_url = 'http://king.tactshan.com/pay/alipay/notify_url';
    public $return_url = 'http://king.tactshan.com/pay/alipay/return_url';
    public $rsaPrivateKeyFilePath = './key/priv.key';
    public $aliPubKey = './key/ali_pub.key';

    public function __construct()
    {
        if(empty($_COOKIE['uid'])){
            $this->middleware('auth');
        }
    }
    /**
     * 请求订单服务 处理订单逻辑
     *
     */
    public function test0()
    {
        //
        $url = 'http://vm.order.lening.com';
        // $client = new Client();
        $client = new Client([
            'base_uri' => $url,
            'timeout'  => 2.0,
        ]);

        $response = $client->request('GET', '/order.php');
        echo $response->getBody();


    }


    public function test(Request $request,$order_num)
    {
        $where=[
            'order_num'=>$order_num
        ];
        $detailInfo=DetailModel::where($where)->get();
        if(empty($detailInfo)){
            exit('请选择要结算的订单');
        }
        $total_amount=0;
        $str='';
        foreach ($detailInfo as $k=>$v){
            $total_amount+=$v->goods_price*$v->buy_number;
            $str.=$v->goods_name.',';
        }
        $str=rtrim($str,',');
        $bizcont = [
            'subject'           => $str,
            'out_trade_no'      => $order_num,
            'total_amount'      =>$total_amount/100,
            'product_code'      => 'QUICK_WAP_WAY',

        ];

        $data = [
            'app_id'   => $this->app_id,
            'method'   => 'alipay.trade.wap.pay',
            'format'   => 'JSON',
            'charset'   => 'utf-8',
            'sign_type'   => 'RSA2',
            'timestamp'   => date('Y-m-d H:i:s'),
            'version'   => '1.0',
            'notify_url'   => $this->notify_url,
            'return_url'=>$this->return_url,
            'biz_content'   => json_encode($bizcont),
        ];
        $sign = $this->rsaSign($data);
        $data['sign'] = $sign;
        $param_str = '?';
        foreach($data as $k=>$v){
            $param_str .= $k.'='.urlencode($v) . '&';
        }
        $url = rtrim($param_str,'&');
        $url = $this->gate_way . $url;

        header("Location:".$url);
    }


    public function rsaSign($params) {
        return $this->sign($this->getSignContent($params));
    }

    protected function sign($data) {

        $priKey = file_get_contents($this->rsaPrivateKeyFilePath);
        $res = openssl_get_privatekey($priKey);

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);

        if(!$this->checkEmpty($this->rsaPrivateKeyFilePath)){
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }


    public function getSignContent($params) {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, 'UTF-8');
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }


    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset) {

        if (!empty($data)) {
            $fileType = 'UTF-8';
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }


        return $data;
    }

    /**
     * 支付宝同步通知回调
     *
     */
    public function return_url(){

        $data=$_GET;
        //验证订单号
        $orderWhere=[
            'order_num'=>$_GET['out_trade_no']
        ];
        $orderInfo=OrderModel::where($orderWhere)->first();
        if(empty($orderInfo)){
            exit('订单不存在');
        }
        //验证订单金额
        if($orderInfo['order_amount']/100!=$_GET['total_amount']){
            exit("订单金额有误");
        }
        //验签 支付宝的公钥
        if(!$this->verify($data)){
            echo 'error';
        }
        $info=[
            'orderInfo'=>$orderInfo
        ];
        return view('pay.paysuccess',$info);
    }
    /**
     * 支付宝支付异步通知
     *
     */
    public function notify_url(Request $request){
        $data = json_encode($_POST);
        $log_str = '>>>> '.date('Y-m-d H:i:s') . $data . "<<<<\n\n";
        //记录日志
        file_put_contents('logs/alipay.log',$log_str,FILE_APPEND);
        //验签
        $res = $this->verify($_POST);

        $log_str = '>>>> ' . date('Y-m-d H:i:s');
        if($res === false){
            //记录日志 验签失败
            $log_str .= " Sign Failed!<<<<< \n\n";
            file_put_contents('logs/alipay.log',$log_str,FILE_APPEND);
        }else{
            $log_str .= " Sign OK!<<<<< \n\n";
            file_put_contents('logs/alipay.log',$log_str,FILE_APPEND);
        }
        //处理订单逻辑
        $this->dealOrder($_POST);

        echo 'success';

    }
    //验签
    function verify($params) {
        $sign = $params['sign'];
        $params['sign_type'] = null;
        $params['sign'] = null;

        //读取公钥文件
        $pubKey = file_get_contents($this->aliPubKey);
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        //转换为openssl格式密钥

        $res = openssl_get_publickey($pubKey);
        ($res) or die('支付宝RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值

        $result = (openssl_verify($this->getSignContent($params), base64_decode($sign), $res, OPENSSL_ALGO_SHA256)===1);
        openssl_free_key($res);

        return $result;
    }

    protected function rsaCheckV1($params, $rsaPublicKeyFilePath,$signType='RSA') {
        $sign = $params['sign'];
        $params['sign_type'] = null;
        $params['sign'] = null;
        return $this->verify($this->getSignContent($params), $sign, $rsaPublicKeyFilePath,$signType);
    }

    /**
     * 处理订单逻辑 更新订单 支付状态 更新订单支付金额 支付时间
     * @param $data
     */
    public function dealOrder($arr)
    {
        $where=[
            'order_num'=>$arr['out_trade_no']
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
        $data=[
            'order_status'=>2
        ];
        $orderInfo=OrderModel::where($where)->first();
        $amount=$orderInfo->order_amount;
        $userWhere=[
            'uid'=>$orderInfo->uid,
        ];
        //加积分
        $userInfo=UserModel::where($userWhere)->first();
        $userDate=[
            'sort'=>$userInfo->sort+$amount
        ];
        $res=UserModel::where($userWhere)->update($userDate);
        OrderModel::where($where)->update($data);
        //修改订单时间
        $orderData=[
            'pay_time'=>time()
        ];
        $res=OrderModel::where($where)->update($orderData);
        return true;
    }
    /**
     * 删除订单
     */
    public function deleteOrder(){
        $orderInfo=OrderModel::all();
        if(empty($orderInfo)){
            exit('还没有下单');
        }
        $orderInfo=$orderInfo->toArray();
        foreach ($orderInfo as $k=>$v){
            if($v['order_status']==1){
                if(time()-$v['add_time'] > 300){
                    $orderWhere=['order_num'=>$v['order_num']];
                    $data=[
                        'order_status'=>3
                    ];
                    $res=OrderModel::where($orderWhere)->update($data);
                    $detailInfo=DetailModel::where($orderWhere)->get();
                    foreach ($detailInfo as $k=>$v) {
                        $info=[
                            'status'=>2
                        ];
                        $res2=DetailModel::where($orderWhere)->update($info);
                    }
                }
            }
        }

        echo "success"."\n";
    }


}
