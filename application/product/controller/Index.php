<?php
namespace app\product\controller;

use think\Controller;
use think\Db;
use \think\Request;
class Index extends Controller
{
    public function __construct(){
        parent::__construct();
        $db = Db::connect();
    }

    public function index()
    {

       return $this->fetch();
    }


    public function details()
    {
        $request = Request::instance();
        $data = Db::name('product')->find($request->param('id'));
        if(!$data){
            $this->error('参数有误，请重新进入！');
        }

        $this->assign('info',$data);
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
        $info = Db::name('product')->select();

//        Db::connect('mysql://huajiantang:huajiantang2017@47.93.231.146:3306/flower#utf8');
        var_dump($info);
    }

}
