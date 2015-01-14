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
        //获得随机验证串
    function get_token() {
        $token = '';
        while (strlen($token) < 32) {
            $token .= mt_rand(0, mt_getrandmax());
        }
        $token = md5(uniqid($token, TRUE));
        return $token;
    }
    //根据机器码获取用户信息
    public function getusernameBymachinenum($key){
        $data = $this->app_model->get_one(array('machinenum' => $key));
        if($data){
            return $data['username'];
        }else{
            return "";
        }
    }


    //区域经理登陆调用接口
    public function login() {
        $username = safe_replace(filter_input(INPUT_GET, 'username'));
        $password = safe_replace(filter_input(INPUT_GET, 'password'));
        //$machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        if ($data = $this->app_model->get_one(array('username' => $username))) {
            $password = md5(md5($password) . $data['encrypt']);
            if ($password != $data['password']) {
                echo json_encode(array("result" => 0,"token"=>0)); //密码不正确
            } elseif ($password == $data['password']) {
                $token=  $this->get_token();
                $this->app_model->update(array('lastloginip' => ip(), 'lastlogintime' => SYS_TIME,'token'=>$token), array('userid' => $data['userid']));
                
                echo json_encode(array("result" => 1,"token"=>$token,"shopname"=>$data['shopname'],"shopaddr"=>$data['shopaddr'],"machinenum"=>$data['machinenum'])); //成功
            }
        } else {
            echo json_encode(array("result" => -1,"token"=>0)); //帐号不存在
        }
    }
    //首页产品显示Product display
    public function productDisplay(){
        $dateline = safe_replace(filter_input(INPUT_GET, 'dateline'));
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        if (empty($dateline)) {
            $dateline = 0;
        }
        if($machinenum){
            $username=  $this->getusernameBymachinenum($machinenum);
        }else{
            $username="";
        }
        if($username){
            $sql = "select ah_chanpin.id,title,thumb,username,updatetime,price,ah_chanpin_data.content from ah_chanpin left join ah_chanpin_data on ah_chanpin.id= ah_chanpin_data.id where updatetime >'$dateline' and  username='$username' order by listorder desc " ;
        }else{
            $sql = "select ah_chanpin.id,title,thumb,username,updatetime,price,ah_chanpin_data.content from ah_chanpin left join ah_chanpin_data on ah_chanpin.id= ah_chanpin_data.id where updatetime >'$dateline'  order by listorder desc " ;
        }
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if($data){
            $allData['data']=$data;
        }else{
            $allData['data']=array();
        }
        //将状态改为非最新状态
        $newdata = getcache("isnew" , "isnew");
         if($newdata){
            $isnewdata['product_isnew']=0;
            $isnewdata['expert_isnew']=$newdata['expert_isnew'];
         }  else {
            $isnewdata['product_isnew']=0;
            $isnewdata['expert_isnew']=1;
         }
          setcache("isnew", $isnewdata, "isnew");
        echo json_encode($allData);  
    }
    
     //首页专家显示 expert display
    public function expertDisplay(){
         $dateline = safe_replace(filter_input(INPUT_GET, 'dateline'));
         if (empty($dateline)) {
            $dateline = 0;
        }
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        if($machinenum){
            $username=  $this->getusernameBymachinenum($machinenum);
        }else{
            $username="";
        }
        if($username){
            $sql = "select ah_expert.id,title,thumb,username,updatetime,worktime,ah_expert_data.content from ah_expert left join ah_expert_data on ah_expert.id=ah_expert_data.id  where updatetime>'$dateline' and  username='$username' order by listorder desc " ;
        }  else {
            $sql = "select ah_expert.id,title,thumb,username,updatetime,worktime,ah_expert_data.content from ah_expert left join ah_expert_data on ah_expert.id=ah_expert_data.id  where updatetime>'$dateline'  order by listorder desc " ;
        }
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if($data){
            $allData['data']=$data;
        }else{
            $allData['data']=array();
        }
        //将状态改为非最新状态
         $newdata = getcache("isnew" , "isnew");
         if($newdata){
            $isnewdata['product_isnew']=$newdata['product_isnew'];
            $isnewdata['expert_isnew']=0;
         }  else {
            $isnewdata['product_isnew']=1;
            $isnewdata['expert_isnew']=0;
         }
          setcache("isnew", $isnewdata, "isnew");
         
        echo json_encode($allData);  
    }
    //检查产品与专家是否更新
    public function  isnew(){
         $newdata = getcache("isnew" , "isnew");
         echo json_encode($newdata);
    }

   

}
