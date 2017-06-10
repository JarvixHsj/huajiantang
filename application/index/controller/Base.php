<?php

	namespace app\Index\controller;
	use \think\Controller;
	//use think\Request;

	class Base extends Controller
	{
		public function _initialize()
		{
			parent::_initialize();
		}

		public function _empty()
		{
			return $this->fetch('/user_center');
		}

		
	}
