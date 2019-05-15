<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src="/js/jquery1.7.min.js"></script>
</head>
<body>
    <form action="/admin/menuAddDo" method="POST" enctype="multipart/form-data">
        菜单名字：&nbsp;<input type="text" name="menu_name" placeholder="请输入菜单名" style="width:10%;height:35px;"><br><br>
        菜单父类：
        <select name="p_id" class="top" style="width:10%;height:30px;">
            <option>--请选择所属菜单--</option>
            <option value="0">--顶级菜单--</option>
            @foreach($data as $v)
            <option value="{{$v['menu_id']}}">{{$v['menu_name']}}</option>
            @endforeach
        </select><br><br>
        菜单类型：<input type="text" name="menu_type" style="width:10%;height:35px;" placeholder="请选择菜单类型"><br><br>
        <div id="key">key 键值：&nbsp;&nbsp;<input type="text" name="menu_key" style="width:10%;height:35px;" placeholder="请输入key值"></div>
        <br>
        <input type="submit" value="提交 & 上传菜单" style="color:#2b2b2b;background-color:#0d6aad;width:150px;height:35px;font-size:20px;border:none;"><br>
    </form>
</body>
</html>
<script>
    /*$(function(){
        $('.top').change(function(){
            var a = $(this).children("option:selected").val()
            if(a == 0){
                $('#key').hide();
            }else{
                $('#key').show();
            }
        })
    })*/
</script>
