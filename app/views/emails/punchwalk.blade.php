<?php
$fft = FFT::find($fft['id']);
$item = JobItem::find($item['id']);
?>
There has been a punch item added to a job that has already been signed off on.
<br/><br/>
<b>Customer: </b>{{$fft->job->quote->lead->customer->name}}<br/>
<b>Job ID:</b> {{$fft->job->id}}
<hr/>
<b>Item Details:</b>
<br/><br/>
{{$item->reference}} - {{$item->orderable ? "Orderable Item" : "Item is not orderable"}} -
{{$item->replacement ? "This is a replacement item" : "Not a replacement item."}}
