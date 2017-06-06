<?php
namespace app\product\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Url;
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

    /**
     * 加入购物车
     * @return [type] [description]
     */
    public function join_shopcar()
    {
        $returnUrl = Url::build('index');

        $accept_time    =   input('post.accept_time');     //收花时间
        $spec   =   input('post.spec');                         //规格
        $area   =   input('post.area');                               //区域
        $num    =   input('post.num');                                   //数量
        $projectId = input('post.project_id');  
        $projectInfo = Db::name('product')->find($projectId);

        if(!$projectInfo || !isset($accept_time) || !isset($spec) || !isset($area) || !isset($num)){
            return json(['status' => 0, 'message' => '网络错误，添加失败，请重试！', 'url' => $returnUrl]);
        }
        $data = [
            'accept_time' => $accept_time,
            'spec' => $spec,
            'area' => $area,
            'produce_num' => $num,
            'product_id' => $projectId,
            'product_price' => $projectInfo['price']*$num,
            'add_time' => time(),
            'user_id' => 1,
        ];
        Db::name('user_shopcar')->insert($data);
        $shopcarId = Db::name('user_shopcar')->getLastInsID();
        if($shopcarId){
            return json(['status' => 1,'message'=>'添加购物车成功！']);
        }else{
            return json(['status' => 0, 'message' => '网络错误，添加失败，请重试！', 'url' => $returnUrl]);
        }

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

    public function test_skip()
    {
            $this->error('网络错误，请重新打开！', "index", '', 2);
    }

}
