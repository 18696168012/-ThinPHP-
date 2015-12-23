<?php

class IndexAction extends CommentAction {
    public function index(){
        echo $_SERVER['HTTP_HOST'].'<br/>';
        echo $_SERVER['REQUEST_URI'].'<br/>';
        $table=M("a_user");
        dump($table);
    }

    //消费记录查询
    public function expenseDtail(){
    	$this->display();
    }
    
    //会员卡绑定
    public function banding(){
        $this->getOpenid();
        $openid=$_SESSION['openid'];
        $wap['openid']=$openid;
        $wap['sk']=$_GET['skey'];
        $result=M("wx_band")->where($wap)->find();
        if($result){
            $url="http://wx.ffyydd.com/lkd/index.php/Query/index.html?skey=".$_GET['skey'];
            header("location:".$url);
        }else{
            $this->assign("skey",$_GET['skey']);
            $this->assign("openid",$openid);
            $this->display();
        }
    }
    
    //获取验证码
    public function getVerify(){
        $re=$this->checkPhone($_POST['phone']);
        if($re){
            $verify=mt_rand(100000,999999);
            //$verify='';
            setcookie($_POST['phone'],$verify,time()+120);
            $this->SMS($_POST['phone'],$verify);
            $this->ajaxReturn(0,'发送成功',1);
        }else{
            $this->ajaxReturn(0,'请输入正确的手机号码',0);
        }
    }

    //验证绑定表单
    public function checkForm(){
        if($_COOKIE[$_POST['phone']] != $_POST['verify']){
            $this->ajaxReturn(0,'验证码错误',0);
        }else{
            setcookie("verify");
            $data['openid']=$_POST['openid'];
            $data['phone']=$_POST['phone'];
            $data['sk']=$_POST['skey'];
            $st=M("d_vip")->where("vphone='".$data['phone']."' and skey='".$data['sk']."'")->find();
            if(!$st){
                $this->ajaxReturn(0,'您还未办理会员，请先办理会员',2);exit;
            }
            $data['pubtime']=time();
            $table=M("wx_band");
            $re=$table->where("phone='".$_POST['phone']."' and sk = '".$_POST['skey']."'")->find();
            if($re){
                $this->ajaxReturn(0,'您已绑定！',0);
                exit;
            }
            $result=$table->add($data);
            if($result){
                $this->ajaxReturn(0,'绑定成功',1);
            }else{
                $this->ajaxReturn(0,'网络繁忙，请稍后再试！',0);
            }
        }
    }

    //检测手机号码格式规范
    private function checkPhone($phone){
       if(preg_match("/^1[3|4|5|8|7]{1}\d{9}$/",$phone)){    
            return true;    
        }else{
            return false;
        }
    }

    //短信接口
    private function SMS($phone,$newPwd,$url="http://www.stongnet.com/sdkhttp/sendsms.aspx"){
        $str = "您的验证码是：".$newPwd."。请不要把验证码泄露给其他人。请在1分钟内完成操作，否则失效。如非本人操作，可不用理会！【来客多】";
        $data="reg=101100-WEB-HUAX-041546&pwd=ODTAKDRZ&sourceadd=&phone={$phone}&content={$str}";
        $ch = curl_init();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        $return = curl_exec ( $ch );
        // $output = curl_exec($curl);
        curl_close($ch);
    }

    /*app上显示绑定用户页面开始*/
	public function appbanding(){
        $wx_band=M('wx_band');
        //折线图绑定用户数(判断是否在这天内绑定的)\
        //一天的开始时间
        $start=strtotime(date('Ymd',time()).'00:00:00');
        //一天的结束时间
        $end=strtotime(date('Ymd',time()).'23:59:59');
        $arr=array();
        for($i=0;$i<5;$i++){
        	//前几天的开始结束时间
        	$start=$start-$i*60*60*24;
        	$end=$end-$i*60*60*24;
        	//根据时间去查询绑定用户数
        	$map['pubtime']=array("between",array($start,$end));
        	//存储绑定用户数
        	$arr[]=M("wx_band")->where($map)->select();
        }
        //直线图绑定用户数
        $band_count=array();
        foreach($arr as $k=>$v){
        	$band_count[]=count($v);
        }
        //折线图结束
        //微信绑定用户总数
        $count=$wx_band->count();
        //查询绑定用户的信息
        $skey='844dc205ba';
        $wx_band=M('wx_band');
        //根据ex_band表里面手机号去查询店铺
        $arr=$wx_band->join('d_vip on wx_band.phone = d_vip.vphone')->limit(0,5)->order('pubtime desc')->select();
        $userinfo=array();
        foreach ($arr as $k => $v){
            //判断是否是当前店铺(通过skey)          
            if($v['skey']==$skey){
                $userinfo[]=array('title'=>$v['vname'],'date'=>date('Y-m-d',$v['pubtime']));
            }
        }
        //dump($userinfo);
        //折线图绑定用户数发送到前台模板(5天之内的)
        $this->assign('c1',$band_count[0]);
        $this->assign('c2',$band_count[1]);
        $this->assign('c3',$band_count[2]);
        $this->assign('c4',$band_count[3]);
        $this->assign('c5',$band_count[4]);
        //微信绑定用户总数发送到前台模板
        $this->assign('count',$count);
        //微信绑定用户信息发送到前台模板
        $this->assign('userinfo',$userinfo);
		$this->display();
	}
    //下拉刷新ajax回传页面
    public function appbanding_ajax(){
        //接收ajax传送过来的a标签个数(就是limit开始数)
        $start=$_GET['start']+1;
        //查询微信绑定用户
        //skey(店铺的唯一标识,根据它,去查询下面的绑定用户)
        //skey需要app传递过来,此处先写死(后期结合app在变为动态)
        //wx_band表和d_vip表
        $skey='844dc205ba';
        $wx_band=M('wx_band');
        //根据wx_band表里面手机号去查询店铺
        $arr=$wx_band->join('d_vip on wx_band.phone = d_vip.vphone')->limit($start,2)->order('pubtime desc')->select();
        if($arr==''){
            //如果没有数据,则设置为空,配合前台做判断处理
            $userinfo['lists'][]=array('title'=>'','date'=>'');
        }else{
            //组装数组(前台循环遍历)
            $userinfo=array(); 
            foreach($arr as $k => $v){
                if($v['skey']==$skey){
                    $userinfo['lists'][]=array('title'=>$v['vname'],'date'=>date('Y-m-d',$v['pubtime']));
                }
            }
        }
        $data=json_encode($userinfo);
        echo $data;    
    }
    /*app上显示绑定用户页面结束*/  
    /*微信消息推送开始*/
    public function sendwx(){
        $appid='wx313dff800dfbe6d3';//小伙子自己变为动态的吧
        $appsecret='ba53e5ea64e2dcb0efd0169af143b4fc';//同上     
        $touser='o1kcqxFwd2177UPu_Jbo_8lt4WO0';//这就是openid(小伙子自己改吧)
        $template_id='I2WAYWqZE-V9hHDa3nTsJEjBDnIl5SN-PAP4mJVfs7k';//这是消息模板ID(可以更改,就是选择不同的消息推送类型)
        $url='http://www.baidu.com';//随便定义,看着办吧
        //数据样例(根据不同的消息模板组装不同的消息格式(具体查看公众号消息模板))
        $data=array(
            'first'=>array('value'=>urlencode("您好,恭喜您绑定成功"),'color'=>"#743A3A"),
            'keyword1'=>array('value'=>urlencode('成功')),
            'keyword2'=>array('value'=>urlencode('001')),
            'remark'=>array('value'=>urlencode('备注:有事请联系客服400'))
        );
        //微信消息推送类(放到了import下)
        import('ORG.Util.Sendwxmessage');
        Sendwxmessage::StartSendMessage($appid, $appsecret,$touser,$template_id,$url,$data,$topcolor='#7B68EE');
    }
    /*微信消息推送结束*/
} 
     