<?php

	namespace app\controller;
	use \think\Controller;

	class Base extends Controller
	{
		public function _initialize()
		{
			parent::_initialize();
		}

		public function _empty()
		{
			return $this->fetch('/index');
		}

		
	}
