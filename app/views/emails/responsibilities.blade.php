<?php
$job = Job::find($job['id']);
?>
Hi, {{$job->quote->lead->customer->name}},
<br/><Br/>
We wanted to let you know that we have finished reviewing your job and are preparing to order your cabinets and/or parts for your job. Please
see the attached list of items that require your attention.

