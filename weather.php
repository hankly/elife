<?php
function getWeatherInfo($cityName){    
	if ($cityName == "" || (strstr($cityName, "+")))
	{        
		return "发送天气+城市，例如'天气深圳'";    
	}//用户查询天气,回复关键词 规则    
	$url = "http://api.map.baidu.com/telematics/v3/weather?location=".urlencode($cityName)."&output=json&ak=44f199aa72feabaa17cccdc3923f30ca";//构建通过百度车联API V3.0查询天气url链接    
	$ch = curl_init();//初始化会话 
	curl_setopt($ch, CURLOPT_URL, $url);//设置会话参数 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//设置会话参数 
	$output = curl_exec($ch);//执行curl会话    
	curl_close($ch);//关闭curl会话    
	$result = json_decode($output, true);//函数json_decode() 的功能时将json数据格式转换为数组。
	    
	if ($result["error"] != 0)
	{        
		return $result["status"];    
	}    
	$curHour = (int)date('H',time());    
	$weather = $result["results"][0];//按照微信公众号开发文档,组建设多图文回复信息
	   
	$weatherArray[] = array("Title" => $weather['currentCity']."今天天气:"."温度:".$weather['weather_data'][0]['temperature'].",".$weather['weather_data'][0]['weather'].","."风力:".$weather['weather_data'][0]['wind'].".", "Description" =>"", "PicUrl" =>"http://elife.daoapp.io/img/bg.jpg", "Url" =>"");
	for ($i = 0; $i < count($weather["weather_data"]); $i++) 
	{        
		$weatherArray[] = array("Title"=>$weather["weather_data"][$i]["date"]."\n".$weather["weather_data"][$i]["weather"]." ".$weather["weather_data"][$i]["wind"]." ".$weather["weather_data"][$i]["temperature"]."","Description"=>"","PicUrl"=>(($curHour >= 6) && ($curHour < 18))?$weather["weather_data"][$i]["dayPictureUrl"]:$weather["weather_data"][$i]["nightPictureUrl"], "Url"=>"");
	}
	return $weatherArray;
}
?>