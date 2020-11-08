<?php
class Base
{
    //定义一些常量
    const APPID = 'wxe54413e66ab9f30b';//公众号的唯一标识
    const SECRET = '66bf6d35fcdbef1345f5599b0f5fa34b';//开发者密钥
    const ACHID = '2020198705056550';//商户号ID
    const KEY = 'kkkkkkuuuuuuu'; //商家支付密钥Key
    const CODEURL = 'https://open.weixin.qq.com/connect/oauth2/authorize?';//获取用户openid时 获取code 要跳转的url地址
    const OPENIDURL = 'https://api.weixin.qq.com/sns/oauth2/access_token?';//获取用户openid时 用code获取openid 要跳转的url地址
    const UNURL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';//统一下单URL地址
    //生成微信支付签名
   public function getSign($arr)
   {
       //先判断是否有空值 有空值去除空值不进行排序
       $arr = array_filter($arr);
       //过滤sign 签名 如果有sign就删除掉
       if(isset($arr['sign'])){
           unset($arr['sign']);
       }
       //用数组健名进行正序排序
       ksort($arr);
       //生成url格式的字符串  key=value
       $str = $this->arrToUrl($arr).'&key='.self::KEY;
       //再拼接可以 进行md5加密  再转为大写生成签名
       return strtoupper(md5($str));
   }

    //获取带有签名的数组
    public function setSign($arr)
    {
        $arr['sign'] = $this->getSign($arr);
        return $arr;
    }

    //数组转url 生成url 如果是中文 要转码
    private function arrToUrl($arr)
    {
        return urldecode(http_build_query($arr));
    }

    //验证微信支付签名
   public function checkSign($arr)
   {
       //先获取签名
       $sign = $this->getSign($arr);
       //对比签名和数组里的签名 是否一致
       if($sign == $arr['sign']){
           return true;
       }else{
           return false;
       }
   }

   //获取用户的openid
    public function getOpenId()
    {
        //先从session中获取openid 或者从数据库中获取openid
        if(isset($_SESSION['openid'])){
            return $_SESSION['openid'];
        }else{
            // 1 用户访问一个地址 先获取到一个code
            // 2 根据code获取到openid
            //如果没有code 就先构建地址跳转
            if(!isset($_GET['code'])){
                 //获取跳转本地址的url
                 $redurl = $_SERVER['REQUEST_SCHEME'].'//'. $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
                 $url = self::CODEURL."appid=".self::APPID."&redirect_uri=".$redurl."&response_type=code&scope=snsapi_base &state=STATE#wechat_redirect";
                 header("location:{$url}");
            }else{
//                print_r($_GET);
                //调用接口获取openid
                $openidurl = self::OPENIDURL."appid=".self::APPID."&secret=".self::SECRET."&code=".$_GET['code']."&grant_type=authorization_code";
                $data = file_get_contents($openidurl);//返回一个json的数据
                $arr = json_decode($data,true);//json格式的数据转为数组格式
                $_SESSION['openid'] = $arr['openid'];
                return $_SESSION['openid'];
            }

        }
    }

    /**
     * 1构建原始数据
     * 2加入签名
     * 3将数据转换为XML
     * 4发送XML格式的数据到接口地址
     * 调用统一下单api
     */
    public function unifiedOrder($oid,$type = false)
    {
        //1构建原始数据
        $params = [
            'appid' => self::APPID,//公众号appid
            'ach_id' => self::ACHID,//商户号id
            'nonce_str' => md5(time()),//随机字符串
            'body' => '微信支付公众号支付测试',//商品描述
            'out_trade_no' => $oid,//订单号
            'total_fee' => '1',//总价  单位分  0.01
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],//客户端IP
            'notify_url' => 'www.weixinpay.com/test/notify.php',//支付回调通知URL
            'trade_type' => 'JSAPI',//支付类型  JSAPI支付（或小程序支付）、NATIVE--Native支付、APP--app支付，MWEB--H5支付，
            'product_id' => $oid,//产品ID/订单号
//            'openid'     => $this->getOpenId()
        ];
        if($type == 'h5'){
            $params['trade_type'] = 'MWEB';
        }else{
            $params['openid'] = $this->getOpenId();
        }
        //2获取签名
        $params = $this->setSign($params);
        //3将数据转换为XML
        $xmldata = $this->ArrToXml($params);
        $this->logs('logs.txt',$xmldata);
        //4发送XML格式的数据到接口地址
        $resdata = $this->postXml(self::UNURL,$xmldata);//返回一个xml格式的数据
        //将xml格式的数据转为数组
        $arrdata = $this->XmlToArr($resdata);
        return $arrdata;
    }

    //获取prepayid
    public function getPrepayId($oid)
    {
        $arr = $this->getPrepayId($oid);
        return $arr['prepayid'];
    }

    //获取公众号支付所需要的json数据
    public function getJsParams($prepay_id)
    {
        $params = [
            "appId"=>self::APPID,     //公众号名称，由商户传入
            "timeStamp"=>time(),         //时间戳，自1970年以来的秒数
            "nonceStr"=>md5(time()), //随机串
            "package"=>"prepay_id=".$prepay_id,
            "signType"=>'MD5',         //微信签名方式：
           // "paySign"=>"70EA570631E4BB79628FBCA90534C63FF7FADD89" //微信签名
        ];
        $params['paySign'] = $this->getSign($params);//获取微信签名  要再数组外面获取 否则签名会有问题
        return json_encode($params);//返回一个json格式的数据
    }
    //数组转xml
    public function ArrToXml($arr)
    {
        if(!is_array($arr) || count($arr) == 0) return '';

        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

    //Xml转数组
    public function XmlToArr($xml)
    {
        if($xml == '') return '';
        libxml_disable_entity_loader(true);
        $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $arr;
    }

    //封装postxml方法
    public function postXml($url,$xmldata)
    {
        $ch = curl_init();
//        $headers = [
//            //"Content-Type:text/html;charset=UTF-8", "Connection: Keep-Alive"
//        ];
//        $params[CURLOPT_HTTPHEADER] = $headers; //自定义header
        $params[CURLOPT_URL] = $url;    //请求url地址
        $params[CURLOPT_HEADER] = false; //是否返回响应头信息
        $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
        $params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
        $params[CURLOPT_POST] = true;
        $params[CURLOPT_POSTFIELDS] = $xmldata;//'XML或者JSON等字符串';
        $params[CURLOPT_SSL_VERIFYPEER] = false; //安全证书验证
        $params[CURLOPT_SSL_VERIFYHOST] = false; //安全证书验证
        curl_setopt_array($ch, $params); //传入curl参数
        $content = curl_exec($ch); //执行
//        echo $content; //输出登录结果
        curl_close($ch); //关闭连接
        return $content;
    }
    //获取post中的数据方法
    public function getPostData()
    {
        return file_get_contents('php://input');
    }
    //写入日志文件方法
    public function logs($filename,$data)
    {
        file_put_contents('./logs/'.$filename,$data);
    }

}