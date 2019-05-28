<script src="http://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script src="/js/jquery-3.2.1.min.js"></script>
<script>
    $(function(){
        wx.config({
            debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: '{{$arr['appid']}}', // 必填，公众号的唯一标识
            timestamp: '{{$arr['timestamp']}}', // 必填，生成签名的时间戳
            nonceStr: '{{$arr['nonceStr']}}', // 必填，生成签名的随机串
            signature: '{{$arr['signature']}}',// 必填，签名
            jsApiList: ["updateAppMessageShareData"] // 必填，需要使用的JS接口列表
        });
        wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
            wx.updateAppMessageShareData({
                title: 'sha1', // 分享标题
                desc: '傻1 登录', // 分享描述
                link: 'http://1809a.ytw00.cn/webAdmin', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: 'http://1809a.ytw00.cn/imgs/2019-05-09-02-41-47.jpg', // 分享图标
                success: function () {
                    // 设置成功
                }
            })
        });
    })
</script>
