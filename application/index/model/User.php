<?php

	namespace app\Index\model;

	use think\Model;

	class User extends Model
	{


	  	protected $table = 'flower_user';
	  	protected static $user_id;

		protected static function init()
		{
			parent::init();
			if( !self::$user_id)  session('user_info.user_id') ? self::$user_id = session('user_info.user_id') : '2'; 
		}


		public  function add_user(array $post) 
		{
			return  is_array($post) ?  $this->data($post)->save()  :  false;
		}

		public  function find_user_info()
		{
			return $this->where("user_id", self::$user_id)->find();
		}


		public  function edit_address_phone(array $post)
		{
			
			return $this->allowField(true)->update($_POST,['user_id' => self::$user_id]);

		}
		


		public  function user_orders()
		{
			return [
				'all_orders' => db("user_order")->where("user_id",self::$user_id)->select(),
				'wait_pay_orders' => db("user_order")->where(["user_id"=>self::$user_id,"is_paid"=>'0'])->select()
			];
		}	


		public  function shop_car()
		{


			$data = db('user_shopcar')
					->alias('s')
					->join('product p','s.product_id = p.id')
					->select();
			
			foreach($data as $k=>$val) {
				if($val['product_id'] ==1) $data[$k]['product_pic'] = '/qwqw';
				if($val['product_id'] ==2) $data[$k]['product_pic'] = '/123123';
			}

			return $data;
		}


		public function shop_car_count()
		{
			return db('user_shopcar')->where("user_id", self::$user_id)->count();
		}


	}
