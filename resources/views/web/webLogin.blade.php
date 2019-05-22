<!DOCTYPE html>
<html>
<head>
    <title>登录</title>
    <meta charset="utf-8">
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta http-equiv="Access-Control-Allow-Origin" content="*">
    <link href="/css/h5/login.css" type="text/css" rel="stylesheet">
    <link href="/css/h5/global.css" type="text/css" rel="stylesheet">
</head>
<body>
<div class="login">
    <div class="login-title"><p>登录</p>
        <i></i>
    </div>
    <form method="post" action="{{url('/webAuthAdd')}}">
        <div class="login-bar">
            <ul>
                <li><img src="/images/login_user.png"><input type="text" class="text" name="name" placeholder="请输入用户名" /></li>
                <li><img src="/images/login_pwd.png"><input type="password" class="psd" name="pwd" placeholder="请输入确认密码" /></li>
            </ul>
        </div>
        <div class="login-btn">
            <button class="submit" type="submit">登陆</button>
            {{--<a href="register.html"><div class="login-reg"><p>莫有账号，先注册</p></div></a>--}}
        </div>
    </form>
</div>
</body>
</html>
