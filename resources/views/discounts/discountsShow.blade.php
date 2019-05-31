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
<form action="/admin/discountsAdd" method="POST" enctype="multipart/form-data">
    优惠卷名称：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="discounts_name" placeholder="请输入优惠卷名" style="width:12%;height:35px;"><br><br>
    优惠卷数量：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="discounts_number" placeholder="请输入优惠卷数量" style="width:12%;height:35px;"><br><br>
    优惠卷条件：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="discounts_if" placeholder="请输入优惠卷条件" style="width:12%;height:35px;"><br><br>
    优惠卷金额：&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="discounts_money" placeholder="请输入优惠卷金额" style="width:12%;height:35px;"><br><br>
    优惠卷到期时间：&nbsp;<input type="datetime-local" name="discounts_outtime" placeholder="请输入优惠卷到期时间" style="width:12%;height:35px;"><br><br>
    <br>
    <input type="submit" value="添加优惠卷" style="color:#2b2b2b;background-color:#0d6aad;width:150px;height:35px;font-size:20px;border:none;"><br>
</form>
</body>
</html>
