<!Doctype html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>微信JSSDK分享代码</title>
	<meta name="Keywords" content="">
	<meta name="Description" content="">
	<meta name="renderer" content="webkit" />
</head>
<body>
<?php

$appId = '认证的appid';
$appsecret = '认证的appsecret';

$timestamp = time();
$jsapi_ticket = make_ticket($appId,$appsecret);
$nonceStr = make_nonceStr();
$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$signature = make_signature($nonceStr,$timestamp,$jsapi_ticket,$url);

function make_nonceStr()
{
    $codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for ($i = 0; $i<16; $i++) {
        $codes[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
    }
    $nonceStr = implode($codes);
    return $nonceStr;
}

function make_signature($nonceStr,$timestamp,$jsapi_ticket,$url)
{
    $tmpArr = array(
    'noncestr' => $nonceStr,
    'timestamp' => $timestamp,
    'jsapi_ticket' => $jsapi_ticket,
    'url' => $url
    );
    ksort($tmpArr, SORT_STRING);
    $string1 = http_build_query( $tmpArr );
    $string1 = urldecode( $string1 );
    $signature = sha1( $string1 );
    return $signature;
}

function make_ticket($appId,$appsecret)
{
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("access_token.json"));
    if ($data->expire_time < time()) {
        $TOKEN_URL="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appsecret;
        $json = file_get_contents($TOKEN_URL);
        $result = json_decode($json,true);
        $access_token = $result['access_token'];
        if ($access_token) {
            $data->expire_time = time() + 7000;
            $data->access_token = $access_token;
            $fp = fopen("access_token.json", "w");
            fwrite($fp, json_encode($data));
            fclose($fp);
        }
    }else{
        $access_token = $data->access_token;
    }

    // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
    $data = json_decode(file_get_contents("jsapi_ticket.json"));
    if ($data->expire_time < time()) {
        $ticket_URL="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
        $json = file_get_contents($ticket_URL);
        $result = json_decode($json,true);
        $ticket = $result['ticket'];
        if ($ticket) {
            $data->expire_time = time() + 7000;
            $data->jsapi_ticket = $ticket;
            $fp = fopen("jsapi_ticket.json", "w");
            fwrite($fp, json_encode($data));
            fclose($fp);
        }
    }else{
        $ticket = $data->jsapi_ticket;
    }

    return $ticket;
}

?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  /*
   * 注意：
   * 1. 所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
   * 2. 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
   * 3. 完整 JS-SDK 文档地址：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
   *
   * 如有问题请通过以下渠道反馈：
   * 邮箱地址：weixin-open@qq.com
   * 邮件主题：【微信JS-SDK反馈】具体问题
   * 邮件内容说明：用简明的语言描述问题所在，并交代清楚遇到该问题的场景，可附上截屏图片，微信团队会尽快处理你的反馈。
   */
  wx.config({
    appId: '<?=$appId?>',
    timestamp: <?=$timestamp?>,
    nonceStr: '<?=$nonceStr?>',
    signature: '<?=$signature?>',
    jsApiList: [
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
      ]
  });


wx.ready(function () {

     var shareData = {
    title: '分享标题',
    desc: '分享描述',
    link: document.URL,
    imgUrl: '分享缩略图'
  };

     wx.onMenuShareAppMessage(shareData);
     wx.onMenuShareTimeline(shareData);
     wx.onMenuShareQQ(shareData);
     wx.onMenuShareWeibo(shareData);
});
</script>
</body>
</html>