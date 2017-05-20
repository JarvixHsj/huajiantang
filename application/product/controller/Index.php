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

<<<<<<< HEAD

    public function details()
    {
        return $this->fetch('banner');
=======
    public function test()
    {
        // var_dump();
>>>>>>> 6f0e6a1cef38e4a29aca27bb9cf3a02108c4498f
    }
}
