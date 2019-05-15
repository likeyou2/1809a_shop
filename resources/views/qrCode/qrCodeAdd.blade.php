<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <form action="{{url('/admin/qrCodeAddDo')}}" method="post">
        <div class="form-group">
            <label for="exampleInputEmail1">渠道名称</label>
            <input type="text" class="form-control" id="exampleInputEmail1" name="channel_name" placeholder="Name">
        </div>
        <select name="channel_type" style="width: 50%;text-align: center;text-align-last:center">
            <option value="QR_LIMIT_SCENE">整型</option>
            <option value="QR_LIMIT_STR_SCENE">字符串</option>
        </select>
        <div class="form-group">
            <label for="exampleInputEmail1">渠道标识</label>
            <input type="text" class="form-control" name="channel_cation" id="exampleInputEmail1" >
        </div>
        <button type="submit" class="btn btn-default">添加</button>
    </form>
</body>
</html>