<?php
	namespace app\index\controller;
	use \think\Controller;
	use  app\index\controller\Base;
	use  \think\tools\Curl;
	use \think\Loader;
	use think\Db;
	use app\index\model\User;

	class Index extends Base
	{
		private static $model;
		private static $curl;

		public function _initialize(){

			parent::_initialize();

			if( !self::$curl || !self::$model){
				self::$model = new User();
				self::$curl = new Curl();
			}
		}


		public function test()
		{
			$ids = implode(',',$_POST);
			$price_num = Db::query("select sum(product_price) as count from flower_user_shopcar 
						where id in({$ids}) and is_deleted=0 and is_paid=0");
			dump($price_num);
			//select sum(product_price) from flow_user_shopcar where id in(1,2,3,4,5,6,7,8)
		}



		//
	    public function index()
		{
			if(! session('user_info')){

				$this->wechat_info();
			} else {

				//dump($_SESSION);die;
				$this->assign('user_info' ,self::$model->find_user_info());
				return $this->fetch('/user_center');
			}

	    }


		///微信授权
		public function wechat_info() {

			if ( array_key_exists('code', $_GET)) {
				$code = $_GET['code'];
				$data = self::$curl->get("https://api.weixin.qq.com/sns/oauth2/access_token?appid=".config('wechat.APP_ID')."&secret=".config('wechat.APP_SECRET')."&code=".$code."&grant_type=authorization_code");

				$data = json_decode( $data);
				$data = self::$curl->get("https://api.weixin.qq.com/sns/userinfo?access_token=".$data->access_token."&openid=".$data->openid."&lang=zh_CN");
				$data = json_decode($data);

				$arr = array(
					'user_name' => $data->nickname,
					'wechat_nickname' => $data->nickname,
					'wechat_openid'   => $data->openid,
					'user_sex'	   => $data->sex,
					'wechat_province' => $data->province,
					'wechat_city'  => $data->city,
					'wechat_avatar'=> $data->headimgurl,
					'reg_ip' => request()->ip(),
					'reg_time' => time()
				);

				if( $data = Db::name('user')->where("wechat_openid",$data->openid)->find() ){
						session('user_info',$data);
						return $this->fetch('/user_center');

				} else {
					if( $insert_id=Db::table('flower_user')->insertGetId($arr) ) {   //
						if ( $data=Db::name('user')->where("user_id",$insert_id)->find() ) {
							session('user_info', $data);
							return $this->fetch('/user_center');
						}
					}
				}


			} else {

				$REDIRECT_URI = urlencode ("http://". $_SERVER['HTTP_HOST'] . $_SERVER['QUERY_STRING']) .'wechat_info/';

				$state = uniqid().mt_rand().time();
				$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".config('wechat.APP_ID')."&redirect_uri=".$REDIRECT_URI."&response_type=code&scope=snsapi_userinfo&state=".$state."#wechat_redirect";
				ob_start();
				ob_end_flush();
				header ( "Location: {$url}" );
				exit();
			}
		}



	    //修改收货地址
	    public function set_address()
	    {
	    	if($_POST) {
				$_POST['area'] = $_POST['province'] . '-'.$_POST['city']. '-'.$_POST['district'];
				unset($_POST['province']);
				unset($_POST['city']);
				unset($_POST['district']);

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

	    	//$data = self::$model->order_count();

			//echo $data;die;
//	    	if ( self::$model->order_count() > 0) {
//	    		$this->assign('list', self::$model->user_orders());
//	    		return $this->fetch('./allorder');
//	    	} else {
	    		return $this->fetch('./no_order');
	    	//}
	    	
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
	    		return $this->fetch('./shop_list');
	    	}

	    }




		//
		public function edit_shopcar_num()
		{


			if($_POST)
			{
				$price = Db::query("select p.price as price
							from flower_product as p
							left join flower_user_shopcar as s
							on s.product_id=p.id where s.id={$_POST['id']}")[0]['price'];


				if ( Db::table('flower_user_shopcar')->where('id', $_POST['id'])->setField('produce_num',$_POST['num']) )
				{

					if ( Db::table('flower_user_shopcar')->where('id', $_POST['id'])->setField('product_price',$_POST['num'] * $price) ){
						echo json_encode(['code'=>400,'msg'=>'成功！']);
					}

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


	    //UPDATE flower_user_shopcar set is_deleted=0 where id<>0
	     private static  function order_no()
	     {
    	
     		mt_srand((double) microtime() * 1000000);
     		return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
		 }


		public function test_pay()
		{


			//dump($_SERVER);DIE;

			Loader::import('wx_pay.JsApiPay');

			$tools = new \JsApiPay();


			$openId = $tools->GetOpenid();
			//dump($openId);

			$input = new \WxPayUnifiedOrder();
			$input->SetBody("test");
			$input->SetAttach("test");
			$out_trade_no = \WxPayConfig::MCHID. date("YmdHis"). substr( uniqid(),0,3 );
			$input->SetOut_trade_no( $out_trade_no );
			//$input->SetTotal_fee( $info['price'] * 100);
			$input->SetTotal_fee( "1");
			$input->SetTime_start( date("YmdHis") );
			$input->SetTime_expire( date("YmdHis", time() + 600) );
			$input->SetGoods_tag("test");
			$input->SetNotify_url('http://'.$_SERVER['HTTP_HOST'].'//index/respond/');
			$input->SetTrade_type("JSAPI");
			$input->SetOpenid($openId);
			$order =  \WxPayApi::unifiedOrder($input);

			$jsApiParameters = $tools->GetJsApiParameters($order);
			dump($jsApiParameters);
			$this->assign('jsApiParameters',$jsApiParameters);

			echo '<script type="text/javascript">
            function jsApiCall()
            {
                WeixinJSBridge.invoke(
                    "getBrandWCPayRequest",
                    '.$jsApiParameters.',
            function(res){
            WeixinJSBridge.log(res.err_msg);
                alert(res.err_code+res.err_desc+res.err_msg);
            }
            );
            }

            function callpay()
            {
                if (typeof WeixinJSBridge == "undefined"){
                 if( document.addEventListener ){
                document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);
            }else if (document.attachEvent){
                document.attachEvent("WeixinJSBridgeReady", jsApiCall);
                document.attachEvent("onWeixinJSBridgeReady", jsApiCall);
            }
            }else{
                jsApiCall();
                }
            }
            callpay();

    		</script>';

		}


	
	  
	}
