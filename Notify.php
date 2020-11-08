<?php
//引入base类
include './Base.php';

class Notify extends Base
{
    /**
     * 1获取通知数据 json格式数据 -->  要转为数组格式
     * 2验证签名 (用写好的验证方法)
     * 3验证业务结果 (return_code 和 result_code)
     * 4验证用户订单号和金额(out_trade_no 和 total_fee)
     * 5写入日志 修改订单状态 发货.....
     */
    public function __construct()
    {
        //1获取通知数据 json格式数据 -->  要转为数组格式
        $xmlData = $this->getPostData();
        $arr = $this->XmlToArr($xmlData);
        //2验证签名 (用写好的验证方法)
        if($this->checkSign($arr)){
           $this->logs('stat.txt','验证签名成功!');
        }else{
            $this->logs('stat.txt','验证签名失败!');
            die();
        }
        //3验证业务结果 (return_code 和 result_code) //订单号和金额需要根据订单号从数据库里查询
        if($arr['return_code'] == 'ok' && $arr['total_fee'] == 'ok'){
            $this->logs('stat.txt','验证订单和金额成功!');
        }else{
            $this->logs('stat.txt','验证订单和金额失败!');
            die();
        }

        //4验证用户订单号和金额(out_trade_no 和 total_fee)
        if($arr['out_trade_no'] == 'SUCCESS' && $arr['result_code'] == 'SUCCESS'){
            $this->logs('stat.txt','验证业务成功!');
        }else{
            $this->logs('stat.txt','验证业务失败!');
            die();
        }
        //5写入日志 修改订单状态 处理业务逻辑.....
        $this->logs('stat.txt','支付成功!');//修改订单状态后写入日志
        //6成功后要给支付平台返回一个 success状态 和 ok
      $status = `<xml>
                  <return_code><![CDATA[SUCCESS]]></return_code>
                  <return_msg><![CDATA[OK]]></return_msg>
                </xml>`;
//        $status = [
//            'return_code' => 'SUCCESS',
//            'return_msg' => 'OK',
//        ];
        echo $status;
    }


}

$obj = new Notify();