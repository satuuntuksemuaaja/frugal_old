<h4>Change Request Items to be Ordered Summary</h4>
<p>
<table border="1" cellpadding="4" width="90%">
    <tr bgcolor="#a9a9a9">
        <td align="center>"><b>Customer</b></td>
        <td align="center>"><b>ID</b></td>
        <td align="center>"><b>Signed On</b></td>
        <td align="center>"><b>Item</b></td>
        <td align="center>"><b>Charged</b></td>
    </tr>
    @foreach ($itemList as $item)
    <tr>
        <td>{{$item[0]->name}}</td>
        <td>{{$item[1]}}</td>
        <td>{{$item[2]}}</td>
        <td>{{$item[3]}}</td>
        <td>{{$item[4]}}</td>
    </tr>
    @endforeach
</table>
</p>