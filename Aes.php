<?php

/*
 *AES加密在php5的版本中使用的mcrypt_decrypt 函数，
 * 该函数已经在php7.1后弃用了，
 * 取而代之的是openssl的openssl_encrypt和openssl_decrypt，
 * 并且代码也非常精简，下面是示例代码：
 */

class Aes
{
    public $key = '';

    public $iv = '';

    public function __construct($config)
    {
        foreach($config as $k => $v){
            $this->$k = $v;
        }
    }
    //加密
    public function aesEn($data){
        return  base64_encode(openssl_encrypt($data, $this->method,$this->key, OPENSSL_RAW_DATA , $this->iv));
    }

    //解密
    public function aesDe($data){
        return openssl_decrypt(base64_decode($data),  $this->method, $this->key, OPENSSL_RAW_DATA, $this->iv);
    }
}

$config = [
    'key'	=>	'reter4446fdfgdfgdfg', //加密key
    'iv'	=>  md5(time(). uniqid(),true), //保证偏移量为16位
    'method'	=> 'AES-128-CBC' //加密方式  # AES-256-CBC等
];

$obj = new Aes($config);

$res = $obj->aesEn('penghaijian');//加密数据

echo $res;
echo '<hr>';

echo $obj->aesDe($res);//解密数据