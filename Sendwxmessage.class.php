<?php
//微信发送模板消息给用户
class Sendwxmessage{
	public $access_Token='';
	public function StartSendMessage($appid, $appsecrect,$touser,$template_id,$url,$data,$topcolor='#7B68EE'){
		//此处应该对access_token做保存(有待完善,由于触发少,应该不会出现问题)
		$this->accessToken=Sendwxmessage::getToken($appid, $appsecrect);
		$re=Sendwxmessage::Send($touser,$template_id,$url,$data,$topcolor='#7B68EE');
		return $re;
	}
	//发送消息,组装数据
	public function Send($touser,$template_id,$url,$data,$topcolor='#7B68EE'){
		$template = array(
			'touser' => $touser,
			'template_id' => $template_id,
			'url' => $url,
			'topcolor' => $topcolor,
			'data' => $data
		);
		$json_template = json_encode($template);
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->accessToken;
		$dataRes = Sendwxmessage::request_post($url, urldecode($json_template));
		/*if($dataRes['errcode']==0){
			return true;
		}else{
			return false;
		}	*/
		return $dataRes;	
	}
	//获取access_token
	public function getToken($appid,$appsecret){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
		$token = Sendwxmessage::request_get($url);
		//去掉特殊字符,在此不做处理
		//$token = json_decode(stripslashes($token));
		$arr = json_decode($token,true);
		$access_token = $arr['access_token'];
		return $access_token;
	}
	//post发送请求
	public function request_post($url,$param){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
		}
		if (is_string($param)) {
			$strPOST = $param;
		} else {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$strPOST =  join("&", $aPOST);
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($oCurl, CURLOPT_POST,true);
		curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		return $sContent;
	}
	//get发送请求
	public function request_get($url){
		 $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){  
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
	}
}