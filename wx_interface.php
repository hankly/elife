<?php
//装载模板文件
include_once ("wxlibs/WXBizMsgCrypt.php");
include_once("wx_tpl.php");
include_once("base-class.php");
include_once("DB.php");
include_once("weather.php");

define("TOKEN", "weixin");
$encodingAesKey = "F2ftjdmaMJX2Y8tRjRFuBqtFvAt9yQ8OITAza56tdah";
$token = "weixin";
$corpId = "wx50e12c60234a4372";
//新建sae数据库类
//$mysql = new SaeMysql();
$echoStr = $_GET["echostr"];
if (!empty($echoStr)) {
  //valid signature , option
        if(checkSignature($encodingAesKey,$token,$corpId)){
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
      $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);  
    	//解析数据
      $signature = $_GET["msg_signature"];
      $timestamp = $_GET["timestamp"];
      $nonce = $_GET["nonce"];
      $errCode = $wxcpt->DecryptMsg($signature, $timestamp, $nonce, $postStr, $sMsg);
       //   $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
      if ($errCode == 0) {
        // 解密成功，sMsg即为xml格式的明文
        // TODO: 对明文的处理
        // For example:
        $postObj =simplexml_load_string($sMsg);
        //发送消息方ID
        $fromUsername = $postObj->FromUserName;
        //接收消息方ID
        $toUsername = $postObj->ToUserName;
        //消息类型
        $form_MsgType = $postObj->MsgType;
        if($form_MsgType=="event")
        {
            //获取事件类型
            $form_Event = $postObj->Event;
            //点击事件
            if($form_Event=="click")
            {
                //回复欢迎文字消息
                $EventKey=$postObj->EventKey;
                if($EventKey == "weather_jj")
                {
                    $weather_array = getWeatherInfo("靖江");
                    $resultStr=transmitNews($postStr,$weather_array);
                    $sEncryptMsg = ""; //xml格式的密文
                    $errCode = $wxcpt->EncryptMsg($resultStr, $timestamp, $nonce, $sEncryptMsg);
                    if ($errCode == 0) {
                      echo $sEncryptMsg;
                      exit;
                    } else {
                      echo "";
                      exit;
                    }
                }else if($EventKey == "weather_nj"){
                    $rep_content = "今天是 ".date("Y-m-d")."，南京天气很好";
                }else{
                    $rep_content="接口正在开发中....";
                    
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, time(), "text", $rep_content);
                $sEncryptMsg = ""; //xml格式的密文
                $errCode = $wxcpt->EncryptMsg($resultStr, $timestamp, $nonce, $sEncryptMsg);
                if ($errCode == 0) {
                  echo $sEncryptMsg;
                  exit;
                } else {
                  echo "";
                  exit;
                }
            }
        }
        
        
        
      } else {
          echo "";
          exit;
      }
    	          
  }
  else 
  {
          echo "";
          exit;
  }
function checkSignature($encodingAesKey,$token,$corpId)
  {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["msg_signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $echoStr = $_GET["echostr"]; 
        $token = TOKEN;
        $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
        $errCode = $wxcpt->VerifyURL($signature, $timestamp, $nonce, $echoStr, $sEchoStr);
        if ($errCode == 0) {
          //
          // 验证URL成功，将sEchoStr返回
          //HttpUtils.SetResponce($sEchoStr);
          echo $sEchoStr;
          return true;
        } else {
          print("ERR: " . $errCode . "\n\n");
          return false;
        }
  }

function transmitNews($object, $newsArray)
{
    if(!is_array($newsArray)){
        return "";
    }
    $itemTpl = "<item>
                <Title><![CDATA[%s]]></Title>
                <Description><![CDATA[%s]]></Description>
                <PicUrl><![CDATA[%s]]></PicUrl>
                <Url><![CDATA[%s]]></Url>
                </item>
                ";
    $item_str = "";
    foreach ($newsArray as $item){
        $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
    }
    $newsTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[news]]></MsgType>
                <Content><![CDATA[]]></Content>
                <ArticleCount>%s</ArticleCount>
                <Articles>
                $item_str</Articles>
                </xml>";

    $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
    return $result;
}

?>
