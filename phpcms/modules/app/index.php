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
    //根据机器码获取门店对应的所有子id
    public function getcatidBymachinenum($key){
        $sql="select * from category_machine where machenum='$key' limit 1";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if($data){
            $sql="select arrchildid from ah_category where catid=".$data[0]['catid'];
            $this->app_model->query($sql);
            $data = $this->app_model->fetch_array();
            return $data[0]['arrchildid'];
        }else{
            return 0;
        }
    }
    //根据机器码获门店id
   public function getmendianidBymachinenum($key){
        $sql="select * from category_machine where machenum='$key' limit 1";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if($data){
            return $data[0]['catid'];
        }else{
            return 0;
        }
    }


    //区域经理登陆调用接口
    public function login() {
        $username = safe_replace(filter_input(INPUT_GET, 'username'));
        $password = safe_replace(filter_input(INPUT_GET, 'password'));
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        if ($data = $this->app_model->get_one(array('username' => $username))) {
            $password = md5(md5($password) . $data['encrypt']);
            if ($password != $data['password']) {
                echo json_encode(array("result" => 0,"token"=>0)); //密码不正确
            } elseif ($password == $data['password']) {
                $token=  $this->get_token();
                $this->app_model->update(array('lastloginip' => ip(), 'lastlogintime' => SYS_TIME,'token'=>$token), array('userid' => $data['userid']));
                $sql="select c.catname,c.shopaddr,c.catid from category_machine cm left join ah_category c on cm.catid=c.catid where cm.machenum='$machinenum' limit 1";
                $this->app_model->query($sql);
                $mendiandata= $this->app_model->fetch_array();
                if($mendiandata){
                    echo json_encode(array("result" => 1,"token"=>$token,"shopname"=>$mendiandata[0]['catname'],"shopaddr"=>$mendiandata[0]['shopaddr'],"machinenum"=>$machinenum,'name'=>$data['realname'],"mendianid"=>$mendiandata[0]['catid'])); //成功
                }else{
                     echo json_encode(array("result" => 1,"token"=>$token,"shopname"=>"","shopaddr"=>"","machinenum"=>$machinenum,'name'=>$data['realname'],"mendianid"=>0)); //成功
                }
            }
        } else {
            echo json_encode(array("result" => -1,"token"=>0)); //帐号不存在
        }
    }
    //首页产品显示Product display
    public function productDisplay(){
        $dateline = safe_replace(filter_input(INPUT_GET, 'dateline'));//产品列表时间戳
        $deldateline= safe_replace(filter_input(INPUT_GET, 'deldateline'));//删除产品列表时间戳
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        if (empty($dateline)) {
            $dateline = 0;
        }
        if (empty($deldateline)) {
            $deldateline = 0;
        }
        if($machinenum){
            $catids=  $this->getcatidBymachinenum($machinenum);
        }else{
            $catids=0;
        }
        if($catids){
            $sql = "select ah_chanpin.id,title,thumb,username,updatetime,price,ah_chanpin_data.content from ah_chanpin left join ah_chanpin_data on ah_chanpin.id= ah_chanpin_data.id where updatetime >'$dateline' and  catid in($catids) order by updatetime desc " ;
        }else{
             $allData['data']=array();
             echo json_encode($allData);  
             exit;
            
            //$sql = "select ah_chanpin.id,title,thumb,username,updatetime,price,ah_chanpin_data.content from ah_chanpin left join ah_chanpin_data on ah_chanpin.id= ah_chanpin_data.id where updatetime >'$dateline'  order by updatetime desc " ;
        }
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if($data){
            foreach($data as $key=>$val){
                $data[$key]['content']=safe_replace($val['content']);
            }
            $allData['data']=$data;
        }else{
            $allData['data']=array();
        }
//        //将状态改为非最新状态
//        $mendianid=  $this->getmendianidBymachinenum($machinenum);
//        $newdata = getcache("isnew_".$mendianid , "isnew");
//         if($newdata){
//            $isnewdata['product_isnew']=0;
//            $isnewdata['expert_isnew']=$newdata['expert_isnew'];
//         }  else {
//            $isnewdata['product_isnew']=0;
//            $isnewdata['expert_isnew']=1;
//         }
//          setcache("isnew_".$mendianid, $isnewdata, "isnew");
        $sql="select contentid,dateline from ah_del_id where modelid=12 and dateline>'$deldateline' order by dateline asc";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        $max_date=0;
        if($data){
            foreach($data as $ids){
                $id[]=$ids['contentid'];
                $max_date=$ids['dateline'];
            }
            $id_str=  implode(",", $id);
        }else{
            $id_str="";
        }
        $allData['delid']=$id_str;
        $allData['maxdate']=$max_date;
        echo json_encode($allData);  
    }
    
     //首页专家显示 expert display
    public function expertDisplay(){
         $dateline = safe_replace(filter_input(INPUT_GET, 'dateline'));
         $deldateline= safe_replace(filter_input(INPUT_GET, 'deldateline'));//删除产品列表时间戳
         if (empty($deldateline)) {
            $deldateline = 0;
        }
        if (empty($dateline)) {
            $dateline = 0;
        }
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        if($machinenum){
            $catids=  $this->getcatidBymachinenum($machinenum);
        }else{
            $catids=0;
        }
        if($catids){
            $sql = "select ah_expert.id,title,thumb,username,updatetime,worktime,ah_expert_data.content from ah_expert left join ah_expert_data on ah_expert.id=ah_expert_data.id  where updatetime>'$dateline' and  catid in($catids) order by updatetime desc " ;
        }  else {
            //$sql = "select ah_expert.id,title,thumb,username,updatetime,worktime,ah_expert_data.content from ah_expert left join ah_expert_data on ah_expert.id=ah_expert_data.id  where updatetime>'$dateline'  order by updatetime desc " ;
            $allData['data']=array();
             echo json_encode($allData);  
             exit;
            
        }
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if($data){
            foreach($data as $key=>$val){
                $data[$key]['content']=strip_tags(safe_replace($val['content']));
            }
            $allData['data']=$data;
        }else{
            $allData['data']=array();
        }
//        //将状态改为非最新状态
//         $mendianid=  $this->getmendianidBymachinenum($machinenum);
//         $newdata = getcache("isnew_".$mendianid , "isnew");
//         if($newdata){
//            $isnewdata['product_isnew']=$newdata['product_isnew'];
//            $isnewdata['expert_isnew']=0;
//         }  else {
//            $isnewdata['product_isnew']=1;
//            $isnewdata['expert_isnew']=0;
//         }
//          setcache("isnew_".$mendianid, $isnewdata, "isnew");
        $sql="select contentid,dateline from ah_del_id where modelid=14 and dateline>'$deldateline' order by dateline asc";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        $max_date=0;
        if($data){
            foreach($data as $ids){
                $id[]=$ids['contentid'];
                $max_date=$ids['dateline'];
            }
            $id_str=  implode(",", $id);
        }else{
            $id_str="";
        }
        $allData['delid']=$id_str;
        $allData['maxdate']=$max_date;
        echo json_encode($allData);  
    }
    
    //视频中心列表
     public function videoDisplay(){
         $dateline = safe_replace(filter_input(INPUT_GET, 'dateline'));
         if (empty($dateline)) {
            $dateline = 0;
        }
        $videos=array();
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        if($machinenum){
            $catids=  $this->getcatidBymachinenum($machinenum);
        }else{
            $catids=0;
        }
        if($catids){
            $sql = "select ah_video.id,title,updatetime,ah_video_data.video_url from ah_video left join ah_video_data on ah_video.id=ah_video_data.id  where updatetime>'$dateline' and  catid in($catids) order by updatetime desc " ;
        }  else {
             echo json_encode(array());  
             exit;
            //$sql = "select ah_video.id,title,updatetime,ah_video_data.video_url from ah_video left join ah_video_data on ah_video.id=ah_video_data.id  where updatetime>'$dateline' order by updatetime desc " ;
        }
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if($data){
            foreach ($data as $val){
                $pic=  string2array($val['video_url']);
                if(isset($pic[0]['fileurl'])){
                    $video['flvurl']=$pic[0]['fileurl'];
                }else{
                    $video['flvurl']="";
                }
                $video['id']=$val['id'];
                $video['title']=$val['title'];
                $video['updatetime']=$val['updatetime'];
                
                $videos[]=  $video;
            }
        }
        echo json_encode($videos);  
    }
    
    //预留显示display
    public function yuliuDisplay(){
        $dateline = safe_replace(filter_input(INPUT_GET, 'dateline'));
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        $deldateline= safe_replace(filter_input(INPUT_GET, 'deldateline'));//删除产品列表时间戳
        if (empty($deldateline)) {
            $deldateline = 0;
        }
        if (empty($dateline)) {
            $dateline = 0;
        }
        if($machinenum){
            $catids=  $this->getcatidBymachinenum($machinenum);
        }else{
            $catids=0;
        }
        if($catids){
            $sql = "select ah_yuliu.id,title,thumb,username,updatetime,price,ah_yuliu_data.content from ah_yuliu left join ah_yuliu_data on ah_yuliu.id= ah_yuliu_data.id where updatetime >'$dateline' and  catid in($catids) order by updatetime desc " ;
        }else{
            $allData['data']=array();
            echo json_encode($allData);  
            exit;
            //$sql = "select ah_yuliu.id,title,thumb,username,updatetime,price,ah_yuliu_data.content from ah_yuliu left join ah_yuliu_data on ah_yuliu.id= ah_yuliu_data.id where updatetime >'$dateline'  order by updatetime desc " ;
        }
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        if($data){
            foreach($data as $key=>$val){
                $data[$key]['content']=  strip_tags(safe_replace($val['content']));
            }
            $allData['data']=$data;
        }else{
            $allData['data']=array();
        }
        $sql="select contentid,dateline from ah_del_id where modelid=16 and dateline>'$deldateline' order by dateline asc";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        $max_date=0;
        if($data){
            foreach($data as $ids){
                $id[]=$ids['contentid'];
                $max_date=$ids['dateline'];
            }
            $id_str=  implode(",", $id);
        }else{
            $id_str="";
        }
        $allData['delid']=$id_str;
        $allData['maxdate']=$max_date;
        echo json_encode($allData);  
    }
    //检查产品与专家是否更新（暂时弃用）
    public function  isnew(){
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        $mendianid=  $this->getmendianidBymachinenum($machinenum);
         $newdata = getcache("isnew_".$mendianid , "isnew");
         if($newdata){
             echo json_encode($newdata);
         }else{
             echo json_encode(array("product_isnew"=>1,"expert_isnew"=>1));
         }  
    }
    
    //检查广告是否更新（暂时弃用）
    public  function adisnew(){
         $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
         $userid = $this->getuseridBymach($machinenum);
         $newdata = getcache("adisnew_".$userid , "isnew");
         if($newdata){
             echo json_encode($newdata);
         }else{
            echo json_encode(array("ad_isnew"=>1));
         }  
    }


    //广告列表
    public function adList(){
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        $dateline = safe_replace(filter_input(INPUT_GET, 'dateline'));
        if(empty($dateline)){
            $dateline=0;
        }
        $userid = $this->getuseridBymach($machinenum);
        $sql="select spaceid from ah_poster_space where userid= '$userid' limit 1";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        $ads=array();
        if($data){
            $now=time();
            $spaceid=$data[0]['spaceid'];
            $sql="select setting,name from ah_poster where spaceid= '$spaceid' and enddate >'$now' and addtime> '$dateline'";
            $this->app_model->query($sql);
            $data = $this->app_model->fetch_array();
            if($data){
                foreach ($data as $val){
                    $pic=  string2array($val['setting']);
                    if(isset($pic[1]['imageurl'])){
                        $ad['url']=$pic[1]['imageurl'];
                    }else{
                        $ad['url']=$pic[1]['flashurl'];
                    }
                    $ads[]=  $ad;
                }
            }
        }
         //将状态改为非最新状态
         setcache("adisnew_".$userid, array("ad_isnew"=>0), "isnew");
        echo json_encode($ads);
    }
    
    //根据机器编号获取userid
    public function getuseridBymach($machenum){
        $sql="select roleid from category_machine cm left join  ah_category_priv  cp on cm.catid=cp.catid where cm.machenum='$machenum' limit 1";
        $this->app_model->query($sql);
        $data = $this->app_model->fetch_array();
        $userid=1;
        if($data){
            $roleid=$data[0]['roleid'];
            $sql="select userid from ah_admin where roleid='$roleid'";
            $this->app_model->query($sql);
            $data = $this->app_model->fetch_array();
            if($data){
                $userid=$data[0]['userid'];
            } 
        }
        return $userid;
    }
    //检查机器编号是否绑定账号
    public function isbangding(){
        $machinenum = safe_replace(filter_input(INPUT_GET, 'machinenum'));
        $mendianid=$this->getmendianidBymachinenum($machinenum);
        if($mendianid){
            echo json_encode(array("state"=>1));//已经绑定
        }else{
            echo json_encode(array("state"=>0));//未绑定
        }
        
    }
    

   

}
