<?php
$auth = Authorization::find($auth['id'])
?>
Hi {{$auth->job->quote->lead->customer->name}},
<br/><br/>
We have created a list of items that requires your prior authorization before we can begin your job. You can review and sign this authorization form by
<a href='http://www.frugalk.com/job/{{$auth->job_id}}/authsign'>clicking here</a>.
<br/><br/>
Item Details:
<br/><br/>
<table border='1' cellpadding='4'>
    <tr>
        <td align='center'><b>Item</b></td>
    </tr>
    @foreach ($auth->items AS $item)
        <tr>
            <td>{{nl2br($item->item)}}</td>
        </tr>
    @endforeach

</table>
<br/><br/>
If you have any questions please contact our office at 770.460.4331
<br/>
<br/>
Thank You,<br/>
Frugal Kitchens and Cabinets