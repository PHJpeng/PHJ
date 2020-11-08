<?php
//引入Base类
include "./Base.php";

class H5pay extends Base
{
    public function __construct()
    {
       $arr = $this->unifiedOrder(time(),'h5');
//        echo '<pre>';
//        print_r($arr);
        header("location:".$arr['mweb_url']);
    }

}

$obj = new H5pay();