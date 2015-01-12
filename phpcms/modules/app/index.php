<?php

defined('IN_PHPCMS') or exit('No permission resources.');
class index {
    protected $app_model;
    public $cache_path = "template";
    private $nums = 15;

    function __construct() {
        $this->app_model = pc_base::load_model('app_model');
    }

    public function init() {
        //$memberinfo = $this->memberinfo;
        //$userId=$this->_userid;
    }

 
    public function getpages($p) {
        $p = (int) $p;
        $limit = " limit " . ($p - 1) * $this->nums . "," . $this->nums;
        return $limit;
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
        $p = safe_replace(filter_input(INPUT_GET, 'page'));
         if (empty($p)) {
            $p = 1;
        }
        //获取总数量
        $sql = "select count(id) as num from ah_chanpin  ";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if ($data) {
            $total = $data[0]['num'];
        } else {
            $total = 0;
        }
        $sql = "select * from ah_chanpin order by listorder desc " . $this->getpages($p);
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        $allData['pagecount'] = ceil($total / $this->nums);
        if($data){
            $allData['data']=$data;
        }else{
            $allData['data']=array();
        }
        echo json_encode($allData);  
    }
    
     //首页专家显示 expert display
    public function expertDisplay(){
         $p = safe_replace(filter_input(INPUT_GET, 'page'));
         if (empty($p)) {
            $p = 1;
        }
        //获取总数量
        $sql = "select count(id) as num from ah_expert  ";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if ($data) {
            $total = $data[0]['num'];
        } else {
            $total = 0;
        }
        $sql = "select * from ah_expert order by listorder desc " . $this->getpages($p);
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        $allData['pagecount'] = ceil($total / $this->nums);
        if($data){
            $allData['data']=$data;
        }else{
            $allData['data']=array();
        }
        echo json_encode($allData);  
    }

   

}
