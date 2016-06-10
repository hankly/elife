<?php
//装载模板文件
include_once("wx_tpl.php");
include_once("base-class.php");
include_once("DB.php");

define("TOKEN", "weixin");
//新建sae数据库类
//$mysql = new SaeMysql();
$echoStr = $_GET["echostr"];
if (!empty($echoStr)) {
  //valid signature , option
        if(checkSignature()){
          echo $echoStr;
          exit;
        }
}
//新建Memcache类
//$mc=memcache_init();
//获取微信发送数据
$db = new DB();
$xm = "xm";
$ph = "ph";
//$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
$postStr = file_get_contents("php://input");
$help_menu = "回复\"BD\"进行通讯录绑定\n回复\"CZ\"进行查找\n";
  //返回回复数据
if (!empty($postStr)){
          
    	//解析数据
       //   $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    	$postObj =simplexml_load_string($postStr);
    	//发送消息方ID
          $fromUsername = $postObj->FromUserName;
    	//接收消息方ID
          $toUsername = $postObj->ToUserName;
   	 //消息类型
          $form_MsgType = $postObj->MsgType;
          
    	//事件消息
          if($form_MsgType=="event")
          {
            //获取事件类型
            $form_Event = $postObj->Event;
            //订阅事件
            if($form_Event=="subscribe")
            {
              //回复欢迎文字消息
              if(check_user($fromUsername))
              {
                  $msgType = "text";
                  $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "感谢您关注企业通讯录公众平台！[愉快]\n\n企业共享自己的通讯录,回复help查看功能菜单\n");
                  echo $resultStr;
                  exit;  
              }else{
                  $msgType = "text";
                  $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "感谢您关注企业通讯录公众平台！您还未绑定帐号，不能进行查找，回复\"BD\"进行绑定，回复help查看功能菜单\n");
                  echo $resultStr;
                  exit; 
              }
            }
          
          }
          if($form_MsgType=="text")
          {
              //获取用户发送的文字内容并过滤
              $form_Content = trim($postObj->Content);
              //$form_Content = string::un_script_code($form_Content);
              if(!empty($form_Content))
              {
                  if(strtolower($form_Content)=="help")
                  {  
                            
                              //关注未绑定欢迎词
                              $help_str="更多功能正在建设中敬请期待！";
                           
                               //回复文字消息
                              $msgType = "text";
                              $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, $help_menu);
                              echo $resultStr;
                              exit;  
                  }
                  if(strtolower($form_Content)=="bd"){
                      if(check_user($fromUsername))
                      {
                          
                        //提示已经绑定警告
                        $msgType = "text";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "你已经绑定账号，请不要重复操作！");
                        echo $resultStr;
                        exit;  
                      }
                      $msgType = "text";
                      $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "请输入你的姓名以xm开头，输入exit退出操作！");
                      echo $resultStr;
                      exit;
                  }
                  if(substr(strtolower($form_Content),0,2)==$xm){
                      if(check_user($fromUsername))
                      {
                          
                        //提示已经绑定警告
                        $msgType = "text";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "你已经绑定账号1，请不要重复操作！");
                        echo $resultStr;
                        exit;  
                      }
                      $msgType = "text";
                      $name = substr(strtolower($form_Content),2,strlen($form_Content));
                      $openid=$fromUsername;
                      $db->insert($name,$openid);
                      $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "请输入你的电话以ph开头，输入exit退出操作！");
                      echo $resultStr;
                      exit;
                  }
                  if(substr(strtolower($form_Content),0,2)==$ph){
                      //如果mobile不为空则退出
                      $user = check_user($fromUsername);
                      if($user == false || $user['phone'] != "")
                      {
                        //提示已经绑定警告
                        $msgType = "text";
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "输入非法，请重新输入");
                        echo $resultStr;
                        exit;  
                      }
                      $msgType = "text";
                      $phone = substr(strtolower($form_Content),2,strlen($form_Content));
                      //$arr = array('phone'=> $phone);
                      $openid=$fromUsername;
                      $db->update_phone($phone,$openid);
                      $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "绑定通讯录成功！".$fromUsername);
                      echo $resultStr;
                      exit;
                  }
                  if(strtolower($form_Content)=="cz"){
                       //回复文字消息
                        if(check_user($fromUsername))
                        {
                            $msgType = "text";
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "当前只支持以姓名查找，输入姓名以#结束\n\n");
                            echo $resultStr;
                            exit;  
                        }else{
                            $msgType = "text";
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "您还未绑定帐号，不能进行查找，回复\"BD\"进行绑定\n\n");
                            echo $resultStr;
                            exit; 
                        }
                  }
                  if(substr(strtolower($form_Content),strlen($form_Content)-1,strlen($form_Content))=="#"){
                       //回复文字消息
                        if(check_user($fromUsername))
                        {
                            $msgType = "text";
                            $name = substr(strtolower($form_Content), 0,strlen($form_Content)-1);
                            $array = $db->fetch_row("*","where name='$name'");
                            if($array==null){
                              $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "未查到相关通讯录");
                            }else{
                              $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "姓名：".$array['name']."\n"."电话：".$array['phone']."\n");
                            }
                            
                            echo $resultStr;
                            exit;  
                        }else{
                            $msgType = "text";
                            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), $msgType, "您还未绑定帐号，不能进行查找，回复\"BD\"进行绑定\n\n");
                            echo $resultStr;
                            exit; 
                        }
                  }
                  if(strtolower($form_Content)=="exit"){
                        //delete
                  }
              }
              
          }
          
  }
  else 
  {
          echo "";
          exit;
  }
function check_user($fromUsername)
{
    $db = new DB();
    $roster_value=$db->fetch_row("*","where openid='$fromUsername'");
    //如果没有绑定
    if(!$roster_value)
    {
        
        return false;
    }
    //如果已经绑定（误取消重新关注员工）
    else
    {
    return $roster_value;        
    }

}
function checkSignature()
  {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
            
    $token = TOKEN;
    $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
    sort($tmpArr, SORT_STRING);
    $tmpStr = implode( $tmpArr );
    $tmpStr = sha1( $tmpStr );
    
    if( $tmpStr == $signature ){
      return true;
    }else{
      return false;
    }
  }
?>
