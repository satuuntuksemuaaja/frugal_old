<?php
use vl\quotes\QuoteGenerator;

$q = new QuoteGenerator(new Quote);

$common = $q->getSetting('commonSense');
$data = "<div class='page'><center><img src='".public_path()."/logo.png'/></center>";
$data .= "
    <center><h4>Frugals Final Touch...</h4>
    <h5>For your peace of mind</h5></center>
    Frugal Kitchens and Cabinets understands this is a large purchase, things don't always go as planned. So for your
    peace of mind, we have scheduled an extra trip (if necessary) to make sure all of your concerns are handled.
    <br/><br/>
    1. Frugal Representative will schedule a meeting on or after appliances are installed.
<br/><br/>
    2. You must be present for this FINAL walk-through to address any concerns. The second payment is due at this time.
<br/><br/>
    3. Frugal Kitchens will order any final items at this time (if needed).
<br/><br/>
    4. Frugal representative will discuss with homeowner if shoe molding is needed and what the additional cost will be.
<br/><br/>
    5. Frugal representative will contact homeowner when final items have arrived to set-up next available appointment.
<br/><br/>
    6. Frugals Final Touch crew will arrive on scheduled day and complete project. <br/><br/>
    <center><b>Note:</b> <i>Frugal's Final Touch will be completed during normal business hours (Monday - Friday 8am-4pm). Appointments
    must be scheduled by 12pm unless an entire day will be required.</i></center>
<br/><br/>
    7. Homeowner must be present to complete our Frugal Final Touch. Balance is due on completion.
<br/><br/>
    8. Frugal Final Touch checklist will be signed by homeowner confirming completed installation.
<br/><br/>
<br/><br/>
    Customer Signature ___________________________ Date ___________
<br/><br/>
If you have any questions regarding dates or schedule please call the office at 770-460-4331 and we will answer any questions you might have.
<br/><br/>
    Thanks,
<br/><br/>
    Frugal Kitchens and Cabinets
</div>";

echo $data;

