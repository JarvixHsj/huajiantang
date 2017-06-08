<?php

	namespace app\Index\controller;
	use  \think\Controller as think;
	use  \think\tools\Curl;
	use \think\Loader;
	use think\Db;
	use app\Index\model\User;

	class Index extends think
	{	
		

		protected static $model;

		public function __construct(){
			parent::__construct();
			self::$model = new User();
		}

		//
	    public function index()
	    {
	    	session('user_info.user_id','2');	
	    	//$_SESSION['user_info']['user_id'] = '2';
	    	//dump($_SESSION);
	    	$this->assign('user_info' ,self::$model->find_user_info());
	    
	    	return $this->fetch('/user_center');
	    }



	    //修改收货地址
	    public function set_address()
	    {
	    	if($_POST) {
	    		//dump($_POST);die;
	    		foreach ($_POST as $key => $value) {
	    			empty($value)  ?  header("Location:{$_SERVER['HTTP_REFERER']}") : redirect(APP_PATH."/");

	    			$_POST[$key] = trim(strip_tags($value));
	    		}
	    		
	    		if ( $info = self::$model->edit_address_phone($_POST)) {
	    			echo "<script>alert('保存成功');window.history.go(-2)</script>";
	    		}  

	    	} else {

	    		$this->assign('user_info' ,self::$model->find_user_info());
	    		return $this->fetch('./setaddress');
	    	}
	    }



	    public function set_account()
	    {
	    	if($_POST) {

	    	} else {
	    		return $this->fetch('./setaccount');
	    	}
	    	return $this->fetch('./setaccount');
	    }

	    //全部订单
	    public function user_all_orders() 
	    {	

	    	//$data = self::$model->shop_car_count();
	    	if ( self::$model->shop_car_count() > 0) {
	    		$this->assign('list', self::$model->user_orders());
	    		return $this->fetch('./allorder');
	    	} else {
	    		return $this->fetch('./no_order');
	    	}
	    	
	    }


	    //待付款
	    public function user_wait_pay()
	    {
	    	$this->assign('list', self::$model->user_orders()['wait_pay_orders']);
	    	return $this->fetch('./obligation');
	    }

	    //我的购物车
	    public function user_shopcar()
	    {
	    	if($_POST ){
	    		//dump($_POST);die;
	    		if ( Db::table('flower_user_shopcar')->where('id', $_POST['id'])->setField('is_deleted','1') )
	    		{
	    			echo json_encode(['code'=>400,'msg'=>'删除成功！']);
	    		}else {
	    			echo json_encode(['code'=>500,'msg'=>'error']);
	    		}

	    	}else{
	    		$this->assign('list', self::$model->shop_car());
	    		return $this->fetch('./shoplist');
	    	}

	    }

		//
		public function edit_shopcar_num()
		{
			if($_POST)
			{
				if ( Db::table('flower_user_shopcar')->where('id', $_POST['id'])->setField('produce_num',$_POST['num']) )
				{
					echo json_encode(['code'=>400,'msg'=>'成功！']);
				}else {
					echo json_encode(['code'=>500,'msg'=>'error']);
				}
			}
		}

	    //收花日历   修改订单
	    public function user_amend()
	    {
	    	return $this->fetch('./amend');
	    }


	    public function add_user() 
	    {
	    	$arr = [
	    		'user_name' => 'maccha',
	    		'wechat_nickname'=>'maccha',
	    		'wechat_avatar'=>'sas',
	    		'wechat_province'=>'广东',
	    		'wechat_city'=>'广州',
	    		'wechat_openid'=>time(),
	    		'reg_time'=>time(),
	    		'reg_ip'=> request()->ip()
	    	];

	    	session('user_id', db("user")->insertGetId($arr)); 
	    }


	    //UPDATE flower_user_shopcar set is_deleted=0 where id<>0
	


	 //    private static  function order_no()
	 //    {
    	
  //   		mt_srand((double) microtime() * 1000000);
     
  //   		return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		// }



		public function test_pay()
		{

			Loader::import('wxpay.JsApiPay');
			$tools = new \JsApiPay();
			$openId = $tools->GetOpenid();

			//②、统一下单
			$input = new \WxPayUnifiedOrder();
			$input->SetBody("test");
			$input->SetAttach("test");
			$input->SetOut_trade_no( \WxPayConfig::MCHID.date("YmdHis"));
			$input->SetTotal_fee("1");
			$input->SetTime_start(date("YmdHis"));
			$input->SetTime_expire(date("YmdHis", time() + 600));
			$input->SetGoods_tag("test");
			$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
			$input->SetTrade_type("JSAPI");
			$input->SetOpenid($openId);
			$order = \WxPayApi::unifiedOrder($input);
			echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
			printf_info($order);
			$jsApiParameters = $tools->GetJsApiParameters($order);
		}


	
	  
	}
