<?php

	namespace app\index\controller;
	use  \think\Controller as think;
	use  \think\tools\Curl;
	//use   app\model\
	use app\index\Jsticket;
	use \think\Loader;


	class Index extends think
	{	
		
		public function __construct(){
			parent::__construct();
			
		}

	    public function index()
	    {
	    
	    	return $this->fetch('/index');
	    }


	    public function address() 
	    {
	    	return $this->fetch('./address');
	    }




	 //   	public function wechat_info () {	

		// 	if ( isset($_GET['code']) ) {
		// 		$code = $_GET['code'];
		//         $data = Curl::get("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".config('app_id')."&secret=".config('app_secret')."&code=".$code."&grant_type=authorization_code");
		        
		//         $data = json_decode( $data);
		//         $data = Curl::get("https://api.weixin.qq.com/sns/userinfo?access_token=".$data->access_token."&openid=".$data->openid."&lang=zh_CN");
		//        	$data = json_decode($data);
		//        	//dump($data);
		      
		//        	$arr = array(
		//        			'wechat_nickname' => $data->nickname,
		//        			'wechat_openid'   => $data->openid,
		//        			'u_sex'	   => $data->sex,
		//        			'wechat_province' => $data->province,
		//        			'wechat_city'  => $data->city,
		//        			'wechat_avatar'=> $data->headimgurl
		//        		);
		       
		// 	} else {

		// 		//echo config('app_id');exit;
		// 		$url = "";
		// 		$REDIRECT_URI = urlencode($url);    
	 //         	$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".config('app_id')."&redirect_uri=".$REDIRECT_URI."&response_type=code&scope=snsapi_userinfo&state=123123#wechat_redirect";

		//         header("Location:$url");
		// 	}
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

			//获取共享收货地址js函数参数
			//$editAddress = $tools->GetEditAddressParameters();
		}


	
	  
	}
