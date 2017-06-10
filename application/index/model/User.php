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
			//if( !self::$user_id) {
			session('user_id') ? self::$user_id = session('user_id') : self::$user_id=2;
			//}
		}


		public  function add_user(array $post) 
		{
			return  is_array($post) ?  $this->data($post)->save()  :  false;
		}

		public  function find_user_info()
		{
			$data = $this->where("user_id", self::$user_id)->find()->toArray();
			//session('user_info',$data);

			$arr = explode('-',$data['area']);
			$data['province'] = $arr[0];
			$data['city'] = $arr[1];
			$data['district'] = $arr[2];
			session('user_info',$data);
			return $data;
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
			$user_id = self::$user_id;
			$data = $this->query("select s.*,
					p.id as product_id,
					p.name as name,
					p.price as price,
					p.intro as intro
					from flower_user_shopcar as s
					left join flower_product as p
					on s.product_id=p.id where s.user_id=1 and s.is_deleted=0 order by s.id asc");
			
			foreach($data as $k=>$val) {
				if($val['product_id'] ==1) 		$data[$k]['product_pic'] = '/static/product/img/1.jpg';
				if($val['product_id'] ==2) 		$data[$k]['product_pic'] = '/static/product/img/2.jpg';

				if($val['accept_time'] == 0) 	$data[$k]['accept_time'] = '周一收花';
				if($val['accept_time'] == 1) 	$data[$k]['accept_time'] = '周日收花';
				if($val['spec'] ==1)         	$data[$k]['spec'] = '月度4束';
				if($val['spec'] ==4 )			$data[$k]['spec'] = '月度12束+1祝福卡';

			}

			return $data;
		}


		public function order_count()
		{
			return db('user_order')->where("user_id", self::$user_id)->count();
		}


	}
