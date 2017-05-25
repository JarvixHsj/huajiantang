<?php

	namespace app\Index\controller;
	use  \think\Controller as think;
	use  \think\tools\Curl;
	use \think\Loader;


	class Index extends think
	{	
		
		public function __construct(){
			parent::__construct();
			
		}

	    public function index()
	    {
	    
	    	return $this->fetch('/user_center');
	    }


	    public function address() 
	    {

	    	if($_POST) {

	    		foreach ($_POST as $key => $value) {
	    			if ( empty($value) ) {
	    				
	    			}
	    		}

	    	} else {
	    		return $this->fetch('./address');
	    	}
	    	
	    }



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
