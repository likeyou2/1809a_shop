<table border="1">
    <tr>
        <td>id</td>
        <td>name</td>
        <td>count</td>
    </tr>
    @foreach($arr as $k=>$v)
        <tr>
            <td>{{$v['id']}}</td>
            <td>{{$v['name']}}</td>
            <td>{{$v['count']}}</td>
        </tr>
    @endforeach
</table>