<?php

namespace App\Http\Controllers\Pay;

use App\Model\DetailModel;
use App\Model\OrderModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use GuzzleHttp\Client;

class PayController extends Controller
{
    //

    public $app_id = '2016092200571887';
    public $gate_way = "http://openapi.alipaydev.com/gateway.do";
    public $notify_url = 'shop.lening.com/pay/alipay/notify';
    public $rsaPrivateKeyFilePath = './key/priv.key';


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
    public function notify(){
        $data=$_GET;
        //验证订单号
        $orderWhere=[
            'order_number'=>$_GET['out_trade_no']        ];
        $orderInfo=OrderModel::where($orderWhere)->first();
        if(empty($orderInfo)){
            exit('订单不存在');
        }
        //验证订单金额
        if($orderInfo['order_amount']!=$_GET['total_amount']){
            exit("订单金额有误");
        }
        //验证签名
        $config=config('alipay_config');
        require_once EXTEND_PATH . 'alipay/pagepay/service/AlipayTradeService.php';

        $alipaySevice = new \AlipayTradeService($config);
        $result = $alipaySevice->check($data);
        if($result) {//验证成功
            //查询接口
            require_once EXTEND_PATH . 'alipay/pagepay/buildermodel/AlipayTradeQueryContentBuilder.php';
            //商户订单号，商户网站订单系统中唯一订单号
            $out_trade_no =$_GET['out_trade_no'];

            //支付宝交易号
            $trade_no ="";
            //请二选一设置
            //构造参数
            $RequestBuilder = new \AlipayTradeQueryContentBuilder();
            $RequestBuilder->setOutTradeNo($out_trade_no);
            $RequestBuilder->setTradeNo($trade_no);

            $aop = new \AlipayTradeService($config);

            $response = $aop->Query($RequestBuilder);
            if($response->trade_status=='TRADE_SUCCESS'){
                echo "成功";
            }
            return view('pay.paysuccess');
        }
    }
}
