<?php
use Carbon\Carbon;
$data = "
<h2>Frugal Final Touch Payment Report</h2>
<p>This report will show all FFT items that have not been marked as paid. If any of these items have been taken care of
please login to frugalk and click the 'Payment' button in the FFT Board.</p>
<table border='0' cellpadding='4'>
<tr>
<td align='center'><b>Customer</b></td>
<td align='center'><b>Assigned To</b></td>
<td align='center'><b>Scheduled</b></td>
<td align='center'><b>Notes</b></td>
</tr>";
$now = Carbon::now();
$on = 0;
foreach (FFT::whereClosed(false)->wherePayment(false)->whereWarranty(false)->get() AS $fft)
  {
    $on++;
    $color = ($on % 2) ? "#dedede" : "#efefef";
    $diff = $fft->schedule_start->diffInDays($now);
    if ($fft->schedule_start == '0000-00-00 00:00:00') continue;
    if ($diff < 2) continue ; // Only if its more than 2 days.
    if ($diff > 500) continue; // Zero Override.
    $assigned = $fft->assigned ? $fft->assigned->name : "Unassigned";
    $data .= "<tr bgcolor='{$color}'>
  <td>{$fft->job->quote->lead->customer->name}</td>
  <td>{$assigned}</td>
  <td>".$fft->schedule_start->format('m/d/y')."</td>
  <td>".nl2br($fft->notes)."</td>
  </tr>";
  }
$data .= "</table>";

echo $data;
