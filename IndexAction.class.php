<?php
class IndexAction extends CommentAction {
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
     