<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="./js/jquery.min.js"></script>
    <script src="./js/qrcode.min.js"></script>
    <script src="http://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
</head>
<body>
iPhone8
<input style="background: pink;border: 0px;" type="button" name="" id="but" value="分享">
</body>
</html>
<script>

    wx.config({
        debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId:"{{$signInfo['appId']}}", // 必填，公众号的唯一标识
        timestamp: "{{$signInfo['timestamp']}}", // 必填，生成签名的时间戳
        nonceStr: "{{$signInfo['nonceStr']}}", // 必填，生成签名的随机串
        signature: "{{$signInfo['signature']}}",// 必填，签名
        jsApiList: ['updateAppMessageShareData'] // 必填，需要使用的JS接口列表
    });

    //qrcode.clear();
    //qrcode.makeCode('new $wpayurl');

    wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
        $('#but').click(function() {
            wx.updateAppMessageShareData({
                title: 'cc', // 分享标题
                desc: 'dd', // 分享描述
                link: '', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: '', // 分享图标
                success: function () {
                    alert('分享成功')
                }
            })
        })
    });
</script>