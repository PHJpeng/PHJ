<?php
include './Base.php';
//开启session
session_start();
/**
 *  获取用户openid
 *  构建原始数据
 *  加入签名
 *  调用统一下单API
 *  获取到prepay_id
 */
class weixinPay extends Base
{
    public function __construct()
    {
//        //统一下单接口必传参数
//        $arr = [
//            'appid' => 'dfggg',//公众号appid
//            'ach_id' => 'sdgfgd',//商户号id
//            'nonce_str' => '随机字符串',//随机字符串
//            'body' => '888333彭海建',//商品描述
//            'out_trade_no' => '内部订单号',//订单号
//            'total_fee' => '总价',//总价
//            'spbill_create_ip' => '客户端IP',//客户端IP
//            'notify_url' => '支付通知URL',//支付通知URL
//            'trade_type' => '支付类型',//支付类型
//            'product_id' => '产品ID/订单号',//产品ID/订单号
////            'sign' => 'SIGNIEPIEOOGGJOSJODOGJS'//签名
//        ];

        //获取生成签名的数组
//        $this->getSign($arr);
        //获取带有签名的数组
//        $arr = $this->setSign($arr);
//        //验证签名调用
//        if($this->checkSign($arr)){
//            echo '签名验证成功!';
//        }else{
//            echo '签名验证失败!';
//        }
    }
}

$obj = new weixinPay();
//$obj->getOpenId();
//$obj->unifiedOrder();
$prepay_id = $obj->getPrepayId('订单号');
$json = $obj->getJsParams($prepay_id);
?>
<script>
    function onBridgeReady(){
        WeixinJSBridge.invoke(
            // 'getBrandWCPayRequest', {
            //     "appId":"wx2421b1c4370ec43b",     //公众号名称，由商户传入
            //     "timeStamp":"1395712654",         //时间戳，自1970年以来的秒数
            //     "nonceStr":"e61463f8efa94090b1f366cccfbbb444", //随机串
            //     "package":"prepay_id=u802345jgfjsdfgsdg888",
            //     "signType":"MD5",         //微信签名方式：
            //     "paySign":"70EA570631E4BB79628FBCA90534C63FF7FADD89" //微信签名
            // },
            'getBrandWCPayRequest', <?php echo $json;?>,
            function(res){
                if(res.err_msg == "get_brand_wcpay_request:ok" ){
                    // 使用以上方式判断前端返回,微信团队郑重提示：
                    //res.err_msg将在用户支付成功后返回ok，但并不保证它绝对可靠。
                }
            });
    }
    if (typeof WeixinJSBridge == "undefined"){
        if( document.addEventListener ){
            document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
        }else if (document.attachEvent){
            document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
            document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
        }
    }else{
        onBridgeReady();
    }
</script>
