<?php
namespace app\product\controller;

use think\Controller;
class Index extends Controller
{
    public function index()
    {
        // dump(__PUBLIC__);
        
        // dump(ROOT_PATH . '/public/static/product/lib');
       return $this->fetch();
    }
}
