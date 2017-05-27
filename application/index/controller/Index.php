<?php

	namespace app\Index\controller;
	use  \think\Controller as think;
	use  \think\tools\Curl;
	use \think\Loader;
	use app\Index\model\User;

	class Index extends think
	{	
		

		protected static $model;

		public function __construct(){
			parent::__construct();
			self::$model = new User();
			
		}

	    public function index()
	    {
	    	dump($_SESSION);
	    	$data = self::$model->find_user_info();
	    	dump($data);
	    	$this->assign( self::$model->find_user_info());
	    
	    	return $this->fetch('/user_center');
	    }


	    public function address() 
	    {

	    	if($_POST) {

	    		foreach ($_POST as $key => $value) {
	    			empty($value) && isset($_SERVER['HTTP_REFERER']) ?  header("Location:{$_SERVER['HTTP_REFERER']}") : redirect(APP_PATH."/");

	    			$_POST[$key] = trim(strip_tags($value));
	    		}

	    		if ($this->user_data($_POST))   echo '<script>alter(\'保存成功\')</script>';$this->index();

	    	} else {
	    		return $this->fetch('./address');
	    	}
	    	
	    }


	    public function add_user() 
	    {
	    	$arr = [
	    		'wechat_nickname'=>'maccha',
	    		'wechat_avatar'=>'sas',
	    		'wechat_province'=>'广东',
	    		'wechat_city'=>'广州',
	    		'wechat_openid'=>time(),
	    		'reg_time'=>time(),
	    		'reg_ip'=> request()->ip()
	    	];

	    	sesssion('user_id', db("user")->insertGetId($arr)); 
	    }



	    private function user_data(array $post) 
	    {

	    	session('user_id') ? $post['user_id']= session('user_id') : '1';

	    	return db("user")->data($post)->add(); 

	    }



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
