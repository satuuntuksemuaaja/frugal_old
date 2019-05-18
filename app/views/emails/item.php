<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 11/17/15
 * Time: 7:30 AM
 */

echo "The following item has been updated.
<br/>
<b>Customer:</b> {$item->job->quote->lead->customer->contacts()->first()->name}<br/>
<b>Job Item:</b> {$item->reference}<br/><br/>
<b>Office Notes:</b> ". nl2br($item->notes) . "<br/>
<b>Contractor Notes:</b> <span style='font-size:14px; font-weight:bold'>". nl2br($item->contractor_notes) . "</span><br/>
";