<?php

defined('IN_PHPCMS') or exit('No permission resources.');
class index {
    protected $app_model;
    public $cache_path = "template";
    private $pagesize = 15;

    function __construct() {
        $this->app_model = pc_base::load_model('app_model');
    }

    public function init() {
        //$memberinfo = $this->memberinfo;
        //$userId=$this->_userid;
    }

    public function getpages($page) {
	$offset = $this->pagesize*($page-1);
        return $offset;
    }

    //区域经理登陆调用接口
    public function login() {
        $username = safe_replace(filter_input(INPUT_GET, 'username'));
        $password = safe_replace(filter_input(INPUT_GET, 'password'));
        if ($data = $this->app_model->get_one(array('username' => $username))) {
            $password = md5(md5($password) . $data['encrypt']);
            if ($password != $data['password']) {
                echo json_encode(array("result" => 0,"token"=>0)); //密码不正确
            } elseif ($password == $data['password']) {
                $token=get_token();
                $this->app_model->update(array('ip' => ip(), 'lastlogin' => SYS_TIME,'token'=>$token), array('id' => $data['id']));
                echo json_encode(array("result" => 1,"token"=>$token)); //成功
            }
        } else {
            echo json_encode(array("result" => -1,"token"=>0)); //帐号不存在
        }
    }
    //首页产品显示Product display
    public function productDisplay(){
        $page=$username = safe_replace(filter_input(INPUT_GET, 'page'));
        $this->table_name="chanpin";
        
        
    }

   

}
