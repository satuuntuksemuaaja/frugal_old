<?php
$job = Job::find($job['id']);
?>
<center><img src="{{public_path()."/logo.png}}"/></center>
    <h4>Customer Responsibilities</h4>
    <h5>{{$job->quote->lead->customer->name}}</h5>
    <p>Thank you for choosing Frugal Kitchens and Cabinets. Please review the items listed below.</p>
    <ul>
        @foreach($job->quote->responsibilities as $r)
            <li>{{$r->name}}</li>
        @endforeach
    </ul>
