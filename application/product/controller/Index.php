<?php
namespace app\product\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Url;
use think\Session;
class Index extends Controller
{
    public function __construct(){
        parent::__construct();
        $db = Db::connect();
        $userInfo = Db::name('user')->find(1);
        Session::set('user_info', $userInfo);
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
            'user_id' => $_SESSION['think']['user_info']['user_id'],
        ];
        Db::name('user_shopcar')->insert($data);
        $shopcarId = Db::name('user_shopcar')->getLastInsID();
        if($shopcarId){
            return json(['status' => 1,'message'=>'添加购物车成功！']);
        }else{
            return json(['status' => 0, 'message' => '网络错误，添加失败，请重试！', 'url' => $returnUrl]);
        }
    }

    //下单页面 （下一步的操作）
    public function next_orders()
    {
        // Session::set('user_info','100727');
        // var_dump(Session::get());die;
        // var_dump(Request::instance()->session());die;
        $returnUrl = Url::build('buynow'); //成功回调的地址

        $accept_time    =   input('post.accept_time');     //收花时间
        $spec   =   input('post.spec');                         //规格
        $area   =   input('post.area');                         //区域
        $num    =   input('post.num');                                   //数量
        $projectId = input('post.project_id');  
        $projectInfo = Db::name('product')->find($projectId);

        if(!$projectInfo || !isset($accept_time) || !isset($spec) || !isset($area) || !isset($num)){
            return json(['status' => 0, 'message' => '网络错误，添加失败，请重试！', 'url' => $returnUrl]);
        }

        //将信息添加到
        $data = [
            'accept_time' => $accept_time,
            'spec' => $spec,
            'area' => $area,
            'product_num' => $num,
            'product_id' => $projectId,
            'product_price' => $projectInfo['price']*$num,
            'add_time' => time(),
            'price' => $projectInfo['price'],
            'name' => $projectInfo['name'],
        ];
        Session::set('user_shopcar',$data);

        return json(['status' => 1, 'message' => '添加成功', 'url' => $returnUrl]);

    }

    //提交订单  （选择地址下单页面）
    public function buynow()
    {
        // var_dump(Session::get());
        $userInfo = Session::get('user_info');
        $userShopcar = Session::get('user_shopcar');
        $data = array();
        if($userInfo['area'] && $userInfo['detail_address'] && $userInfo['phone_call_people'] && $userInfo['receive_phone_call']){
            $data['status'] = 1;
            $data['user_info'] = $userInfo;
            $data['user_shopcar'] = $userShopcar;
        }else{
            $data['status'] = 0;
            $data['user_info'] = $userInfo;
            $data['user_shopcar'] = $userShopcar;
        }

        $this->assign('data', $data);
        return $this->fetch('submit');
    }

    //收货地址页面
    public function address()
    {
        return $this->fetch('address');
    }

    //保存收货地址
    public function save_address()
    {
        // var_dump($_SERVER);die;
        // 接收信息
        $userInfo = Session::get('user_info');
        $phone_call_people = input('post.name');    //联系人
        $receive_phone_call = input('post.receive_phone_call');    //收货人手机
        $you_phone = input('post.phone_call_people');    //您的手机
        $province = input('post.province');    //省
        $city = input('post.city');    //市
        $district = input('post.district');    //区
        $detail_address = input('post.detailed');     //详细地址

        //判断是否存在
        if(empty($phone_call_people) || empty($receive_phone_call) || empty($you_phone) || empty($province) || empty($city) || empty($district) || empty($detail_address)){
            $this->error('请填写完成信息');
        }

        //组合数据
        $insertData = array();
        $insertData['phone_call_people'] = $phone_call_people;
        $insertData['receive_phone_call'] = $receive_phone_call;
        $insertData['you_phone'] = $you_phone;
        $insertData['area'] = $province.'-'.$city.'-'.$district;
        $insertData['detail_address'] = $detail_address;
        // $insertData['user_id'] = $userInfo['user_id'];
        $resStatus = Db::table('flower_user')->where('user_id', $userInfo['user_id'])->setField($insertData);
        if($resStatus !== false){  //判断是否更新成功
            //重写session里面的值
            $newUserInfo = Db::name('user')->find($userInfo['user_id']);
            Session::set('user_info', $newUserInfo);
            $this->success('保存成功～', 'Index/buynow');
        }
        $this->error('网络错误，请重试！');
    }


    /**
     * 提交订单
     * @return [type] [description]
     */
    public function submitOrder()
    {
        $notice = input('post.notice'); // 是否短信提醒
        $message = input('post.message');   //留言
        $userInfo = Session::get('user_info');
        $userCar = Session::get('user_shopcar');
        if(empty($userCar) || empty($userInfo)){
            $this->error('网络请求失败，请重新打开下单！', "Index/index");
        }

        //查询用户是否存在 判断商品是否存在
        $combo_user = Db::name('user')->find($userInfo['user_id']);
        $combo_product = Db::name('product')->find($userCar['product_id']);
        if(!$combo_user || !$combo_product){
            $this->error('网络连接失败，请重新打开接受授权登陆', "Index/index");
        }

        $insertData = array();
        $insertData['user_id'] = $combo_user['user_id'];    //用户id
        $insertData['product_id'] = $combo_product['id'];   //项目id
        $insertData['receive_address'] = $combo_user['area'].$combo_user['detail_address'];
        $insertData['receive_phone_call'] = $combo_user['receive_phone_call'];
        $insertData['add_time'] = time();
        $insertData['spec'] = $userCar['spec']; //规格
        $insertData['accept_time'] = $userCar['accept_time'];   //收花方式
        $insertData['contacts_person'] = $combo_user['phone_call_people']; //紧急联系人（送花人）
        $insertData['product_price'] = $combo_product['price'];     //项目金额
        $insertData['product_num'] = $userCar['product_num'];     //项目金额
        $insertData['total_price'] = $combo_product['price'] * $userCar['product_num']; //总价格
        $insertData['express_id'] = 0; //总价格
        $insertData['order_sn'] = "HJT".date('YmdHis',time()).mt_rand(0,9).substr(md5(uniqid()),2,3).mt_rand(0,9);
        if($notice) $insertData['notice'] = 1;

        if($message) $insertData['message'] = $message;

        $insert_id = Db::name('user_order')->insertGetId($insertData);
        if($insert_id){
            $this->success('下单成功');
        }

        $this->error('网络参数有误，请重试！');
        //

    }



//     public function test_mysql_connect()
//     {
//         $info = Db::name('product')->select();

// //        Db::connect('mysql://huajiantang:huajiantang2017@47.93.231.146:3306/flower#utf8');
//         var_dump($info);
//     }

    public function test_skip()
    {
            $this->error('网络错误，请重新打开！', "index", '', 2);
    }

}
