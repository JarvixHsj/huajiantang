<?php
namespace app\product\controller;

use think\Controller;
use think\Db;
class Index extends Controller
{
    public function __construct(){
        parent::__construct();
        $db = Db::connect();
    }

    public function index()
    {

        // dump(__PUBLIC__);
        
        // dump(ROOT_PATH . '/public/static/product/lib');
       return $this->fetch();
    }


    public function details()
    {
        return $this->fetch('banner');
    }

    //提交订单
    public function buynow()
    {
        return $this->fetch('submit');
    }

    public function address()
    {
        return $this->fetch('address');
    }


    public function test_mysql_connect()
    {
        $info = Db::name('product_banner')->select();

//        Db::connect('mysql://huajiantang:huajiantang2017@47.93.231.146:3306/flower#utf8');
        var_dump($info);
    }

}
