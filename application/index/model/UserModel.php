<?php

	namespace app\index\model;

	use think\Model;

	class User extends Model
	{


	  	protected $table = 'user';
	  	protected static $user_id;

		protected static function init()
		{
			parent::init();
			if( !self::$user_id)  self::$user_id = session('user_id'); 
		}


		public  function add_user(array $post) 
		{
			return  is_array($post) ?  $this->data($post)->save()  :  false;
		}

		public  function find_user_info($user_id)
		{
			return $this->where("user_id", self::$user_id)->find();
		}


		public  function edit_address_phone(array $post)
		{
			return (array_key_exists('phone', $post) && array_key_exists('receive_address', $post)) ? $this->allowField(['phone','receive_address'])->save($_POST, ['id' => self::$user_id]) : false;

		}
		


		public  function user_orders()
		{
			return [
				'all_orders' => db("order")->where("user_id",self::$user_id)->select(),
				'wait_pay_orders' => db("order")->where(["user_id"=>self::$user_id,"is_paid"=>'0'])->select()
			];
		}	


		public  function shop_car()
		{
			return db("user_shopcar")->where("user_id",self::$user_id)->select();
		}





	}
