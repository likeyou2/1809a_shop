<div>
    <h3>标签名称: <i style="color:#8a2be2;">{{$labelData['label_name']}}</i></h3>
</div>
    <table class="table table-striped">
        <tr>
            <td><input type="checkbox" class="check"></td>
            <td>编号</td>
            <td>用户名</td>
            <td>openid</td>
        </tr>
        @foreach($data as $k=>$v)
            <tr>
                <td><input type="checkbox" openid="{{$v['openid']}}" class="checkbox" labelId="{{$labelData['label_id']}}" ></td>
                <td>{{$v['id']}}</td>
                <td>{{$v['nickname']}}</td>
                <td>{{$v['openid']}}</td>
            </tr>
        @endforeach
    </table>
    <input type="button" id="sub" class="btn btn-primary submit" value="加入标签">
<script>
    $(function(){
        $('.check').click(function(){
            var _this=$(this);
            var stutus=_this.prop('checked')
            $('.checkbox').prop('checked',stutus);
        });
        $('#sub').click(function(){
            var openid = [];
            $('.checkbox:checked').each(function (i) {
                openid[i] = $(this).attr('openid');
            });
            var labelId = $('.checkbox').attr('labelId');
            /*alert(openid);
            alert(labelId);
            return false;*/
            var data = {openid:openid,labelId:labelId};
            $.ajax({
                url:"/admin/openidAdd",
                async:true,
                data:data,
                type:'post',
                success:function (img) {
                    if(img == '分配成功'){
                        window.setTimeout("window.location='/admin/labelShow'",1000);
                    }else{
                        alert('分配失败')
                    }
                }
            })
        })

    })
</script>