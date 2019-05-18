<?php
use vl\core\ScheduleEngine;

// This email goes to the customer.
// We have access to the job property.
$data = null;
$graniteFirst = true;
$data = "<h3>Frugal Kitchens Schedule For Your New Kitchen</h3>
 <h4>The following schedule has been set regarding your Frugal Kitchens Installation. If any other work is being done
 please see your contract for additional scheduling information.</h4>
<hr/>
<center><h3><b> ** Please confirm the dates listed below by clicking the following link: <a style='color:red; font-weight:bold;' href='http://www.frugalk.com/confirm/job/$job->id'>CONFIRM SCHEDULE</a>.</b> ** </h3></center>
<hr/>
<br/><br/>
<table width='100%' border=1 cellpadding=4>
<tr>
<td align='center'><b>Date/Time</b></td>
<td align='center'><b>Contractor</b></td>
<td align='center'><b>What to Expect</b></td>
</tr>";
$note = "<i>Please note we will NOT be at your home at 6:00AM. This early hour is for display on our calendar only! - </i> ";
foreach ($job->schedules()->orderBy('start', 'ASC')->get() AS $schedule)
{
    $expected = nl2br(ScheduleEngine::getQuestionsForDesignation($schedule, true));
    if ($schedule->start->hour <= 6)
    {
        $expected .= $note;
    }
    switch ($schedule->designation_id)
    {
        case 1 : //plumber
            $expected .= ScheduleEngine::plumber($schedule)['email'];
            $expected .= ScheduleEngine::getAddons($schedule, 1);
            break;
        case 2 :
            $expected .= ScheduleEngine::electrician($schedule)['email'];
            $expected .= ScheduleEngine::getAddons($schedule, 2);
            break;
        case 3 :
            $expected .= ScheduleEngine::granite($schedule, $graniteFirst)['email'];
            $graniteFirst = false;
            $expected .= ScheduleEngine::getAddons($schedule, 3);
            break;
        case 4 :
            $expected .= ScheduleEngine::cabinets($schedule)['email'];
            $expected .= ScheduleEngine::getAddons($schedule, 4);
            break;
        case 11:
            $expected .= ScheduleEngine::flooring($schedule)['email'];
            break;
    }
    if (!$schedule->default_email)
    {
        $expected = null;
    }
    if ($schedule->customer_notes)
    {
        $expected .= "<br/><b>Additional Notes:</b><br/><br/>$schedule->customer_notes";
    }
    $designation = (!$schedule->designation) ? "Frugal Kitchens Contractor" : $schedule->designation->name;
    $data .= "
  <tr>
  <td>" . $schedule->start->format('m/d/y h:i a') . " - " . $schedule->end->format('m/d/y h:i a') . "</td>
  <td>" . $designation . "</td>
  <td>$expected</td>
  </tr>";
}
$data .= "</table>";
$data .= "<br/>
If you have any concerns about the dates listed above please contact Frugal Kitchens as soon as possible at 770.460.4331
";

echo $data;
