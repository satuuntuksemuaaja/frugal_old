<?php
/*
      Day 1: Cabinet Installer - Remove and install cabinets - add cabinet style to description. 8am - 6pm
      Day 2: Cabinet Installer - Finish kitchen installation 8am-6p
      Day 2b: (> afternoon granite installer) 4pm-6pm
      Day 3: Manually assign if needed. Any contractor for any reason.
      Day 4: Install granite (granite installer) 10am-12pm - Description: Granite type, edge, sink, backsplash? and how tall, also add sq. ft.
      Day 5: Plumber/Electrician - 8am-12p , list of appliances to be installed
 */
use Carbon\Carbon;

if ($job->start_date == '0000-00-00')
{
    echo BS::callout('danger', "<strong>No Start Date Set</strong> You must set a job start date before assigning contractors.
    Please click back to the job board and set a job start date.");
    return;
}
echo BS::title("Job Schedules", "for " . $job->quote->lead->customer->name . " on " .
    Carbon::parse($job->start_date)->format('m/d/y'));

$headers = ['Day', 'Designation', 'Contractor', 'Default Email', 'Start Time', 'End Time', 'Contractor Notes', 'Customer Notes', 'From Contractor'];
$rows = [];

// If Cabinet ONLY then we need a special schedule with NO designation
if ($job->quote->type == 'Cabinet Only')
{
// Cabinet installer - day 1. // 60 is Everyone dude.
    $dayOne = JobSchedule::whereJobId($job->id)->whereDesignationId(9)->orderBy('start', 'ASC')->whereAux(false)->first();
    $installers = User::get();
    $opts = [];
    $sid = ($dayOne) ? $dayOne->id : 0;
    foreach ($installers AS $installer)
        $opts[] = ['value' => $installer->id, 'text' => $installer->name];
    $opts[] = ['value' => 19, 'text' => 'Steven Langley'];
    $installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                          ->title("Select Shipper")
                          ->linkText(isset($dayOne->designation_id) && $dayOne->designation_id ?
                              $dayOne->user->name : "No Cabinet Shipper Assigned")
                          ->source($opts)->url("/job/{$job->id}/schedule/$sid/day/1/designation/9")->render();
    $color = ($sid && $dayOne->sent) ? 'color-success ' : 'color-info ';
    $startFormat = ($dayOne) ? Carbon::parse($dayOne->start)->format('m/d/y h:i a') : "No Start Set";
    $endFormat = ($dayOne) ? Carbon::parse($dayOne->end)->format('m/d/y h:i a') : "No End Set";
    $startEdit = ($dayOne) ? "<a class='mjax' data-target='#workModal' href='/schedule/$dayOne->id/change/start'>$startFormat</a>" : null;
    $endEdit = ($dayOne) ? "<a class='mjax' data-target='#workModal' href='/schedule/$dayOne->id/change/end'>$endFormat</a>" : null;
    $start = ($dayOne) ? $startEdit : "Set Contractor First";
    $end = ($dayOne) ? $endEdit : "Set Contractor First";
    $checked = ($dayOne && $dayOne->complete) ? "<i class='fa fa-check text-success'></i>" : null;
    $send = ($dayOne) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
            ->icon('exclamation')->url("/schedule/$dayOne->id/send")->render() .
        Button::init()->text("Close Contractor")->color('success btn-xs')
            ->icon('check')->modal('workModal', true)->url("/schedule/$dayOne->id/close")->render() .
        "</span>" : null;

    if (!$dayOne)
    {
        $default = "No schedule found";
    }
    else
    {
        $default = $dayOne && $dayOne->default_email ?
            Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$dayOne->id/default")->icon('check')->render() :
            Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$dayOne->id/default")->icon('times')->render();
    }
    $rows[] = [$color . "<h3>1 $checked</h3>", 'Cabinet Shipping Manager', $default, $installers . $send, $start, $end, null, null, null];
} // only if cabinet only


// Cabinet installer - day 1.
$dayOne = JobSchedule::whereJobId($job->id)->whereDesignationId(4)->orderBy('start', 'ASC')->whereAux(false)->first();
$installers = User::whereDesignationId(4)->orWhere('id', 60)->get();
$opts = [];
$sid = ($dayOne) ? $dayOne->id : 0;
foreach ($installers AS $installer)
    $opts[] = ['value' => $installer->id, 'text' => $installer->name];
$opts[] = ['value' => 19, 'text' => 'Steven Langley'];

$color = ($sid && $dayOne->sent) ? 'color-success ' : 'color-info ';
$installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                      ->title("Select Cabinet Installer")
                      ->linkText(isset($dayOne->designation_id) && $dayOne->designation_id ?
                          $dayOne->user->name : "No Cabinet Installer Assigned")
                      ->source($opts)->url("/job/{$job->id}/schedule/$sid/day/1/designation/4")->render();
$notes = ($dayOne) ? Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
                             ->title("Schedule Notes")
                             ->linkText(($dayOne->notes) ?: "No Notes")
                             ->url("/schedule/$dayOne->id/notes")->render() : null;
$customerNotes = ($dayOne) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
                                     ->title("Schedule Customer Notes")
                                     ->linkText(($dayOne->customer_notes) ?: "No Notes")
                                     ->url("/schedule/$dayOne->id/customer_notes")->render() : null;

$send = ($dayOne) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
                                                        ->icon('exclamation')->url("/schedule/$dayOne->id/send")->render() .
    Button::init()->text("Close Contractor")->color('success btn-xs')
          ->icon('check')->modal('workModal', true)->url("/schedule/$dayOne->id/close")->render() .
    "</span>" : null;
$locked = ($dayOne) ? $dayOne->locked ? "<a class='get' href='/schedule/$dayOne->id/lock'><i class='fa fa-lock'></i></a>" :
    "<a class='get' href='/schedule/$dayOne->id/lock'><i class='fa fa-unlock'></i></a>" : null;
$startFormat = ($dayOne) ? Carbon::parse($dayOne->start)->format('m/d/y h:i a') : "No Start Set";
$endFormat = ($dayOne) ? Carbon::parse($dayOne->end)->format('m/d/y h:i a') : "No End Set";
$startEdit = ($dayOne) ? "<a class='mjax' data-target='#workModal' href='/schedule/$dayOne->id/change/start'>$startFormat</a>" : null;
$endEdit = ($dayOne) ? "<a class='mjax' data-target='#workModal' href='/schedule/$dayOne->id/change/end'>$endFormat</a>" : null;
$start = ($dayOne) ? $startEdit : "Set Contractor First";
$end = ($dayOne) ? $endEdit : "Set Contractor First";
$checked = ($dayOne && $dayOne->complete) ? "<i class='fa fa-check text-success'></i>" : null;
$cnotes = ($dayOne) ? $dayOne->contractor_notes : null;
if ($dayOne)
{
    $default = ($dayOne && $dayOne->default_email) ?
        Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$dayOne->id/default")->icon('check')->render() :
        Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$dayOne->id/default")->icon('times')->render();
}
else $default = null;
if ($job->quote->type == 'Full Kitchen' || $job->quote->type == 'Cabinet and Install' || $job->quote->type == 'Builder')
{
    $rows[] = [$color . "<h3>1 $checked{$locked}</h3>", 'Cabinet Installer', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];
}

// Cabinet Delivery - day 1.

$delivery = JobSchedule::whereJobId($job->id)->whereDesignationId(8)->orderBy('start', 'ASC')->whereAux(false)->first();
$installers = User::whereDesignationId(8)->orWhere('id', 60)->get();
$opts = [];
$sid = ($delivery) ? $delivery->id : 0;
foreach ($installers AS $installer)
    $opts[] = ['value' => $installer->id, 'text' => $installer->name];
$color = ($sid && $delivery->sent) ? 'color-success ' : 'color-info ';
$installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                      ->title("Select Cabinet Delivery Agent")
                      ->linkText(isset($delivery->designation_id) && $delivery->designation_id ?
                          $delivery->user->name : "No Cabinet Delivery Assigned")
                      ->source($opts)->url("/job/{$job->id}/schedule/$sid/day/1/designation/8")->render();
$notes = ($delivery) ? Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
                               ->title("Schedule Notes")
                               ->linkText(($delivery->notes) ?: "No Notes")
                               ->url("/schedule/$delivery->id/notes")->render() : null;
$customerNotes = ($delivery) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
                                       ->title("Schedule Customer Notes")
                                       ->linkText(($delivery->customer_notes) ?: "No Notes")
                                       ->url("/schedule/$delivery->id/customer_notes")->render() : null;
$send = ($delivery) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
                                                          ->icon('exclamation')->url("/schedule/$delivery->id/send")->render() .
    Button::init()->text("Close Contractor")->color('success btn-xs')
          ->icon('check')->modal('workModal', true)->url("/schedule/$delivery->id/close")->render() .
    "</span>" : null;
$locked = ($delivery) ? $delivery->locked ? "<a class='get' href='/schedule/$delivery->id/lock'><i class='fa fa-lock'></i></a>" :
    "<a class='get' href='/schedule/$delivery->id/lock'><i class='fa fa-unlock'></i></a>" : null;
$startFormat = ($delivery) ? Carbon::parse($delivery->start)->format('m/d/y h:i a') : "No Start Set";
$endFormat = ($delivery) ? Carbon::parse($delivery->end)->format('m/d/y h:i a') : "No End Set";
$startEdit = ($delivery) ? "<a class='mjax' data-target='#workModal' href='/schedule/$delivery->id/change/start'>$startFormat</a>" : null;
$endEdit = ($delivery) ? "<a class='mjax' data-target='#workModal' href='/schedule/$delivery->id/change/end'>$endFormat</a>" : null;
$start = ($delivery) ? $startEdit : "Set Contractor First";
$end = ($delivery) ? $endEdit : "Set Contractor First";
$checked = ($delivery && $delivery->complete) ? "<i class='fa fa-check text-success'></i>" : null;
$cnotes = ($delivery) ? $delivery->contractor_notes : null;
if ($delivery)
{
    $default = ($delivery && $delivery->default_email) ?
        Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$delivery->id/default")->icon('check')->render() :
        Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$delivery->id/default")->icon('times')->render();
}
else $default = null;
if ($job->quote->type == 'Full Kitchen' || $job->quote->type == 'Cabinet and Install' || $job->quote->type == 'Builder')
{
    $rows[] = [$color . "<h3>1 $checked{$locked}</h3>", 'Cabinet Delivery', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];
}

// Cabinet installer - day 2.
if ($dayOne)
{
    $dayTwo = JobSchedule::whereJobId($job->id)->whereDesignationId(4)->where('start', '!=', $dayOne->start)
                         ->whereAux(false)->first();
}
else
{
    $dayTwo = null;
}
if ($dayTwo && $dayTwo->start == $dayOne->start)
{
    unset($dayTwo);
} // There's only one schedule for a cabinet so day 2 isn't set.
$installers = User::whereDesignationId(4)->orWhere('id', 60)->get();
$opts = [];
$sid = ($dayTwo) ? $dayTwo->id : 0;
$color = ($sid && $dayTwo->sent) ? 'color-success ' : 'color-info ';
foreach ($installers AS $installer)
    $opts[] = ['value' => $installer->id, 'text' => $installer->name];
$opts[] = ['value' => 19, 'text' => 'Steven Langley'];

$installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                      ->title("Select Cabinet Installer")
                      ->linkText(isset($dayTwo->designation_id) && $dayTwo->designation_id ?
                          $dayTwo->user->name : "No Cabinet Installer Assigned")
                      ->source($opts)->url("/job/{$job->id}/schedule/$sid/day/2/designation/4")->render();
$startFormat = ($dayTwo) ? Carbon::parse($dayTwo->start)->format('m/d/y h:i a') : "No Start Set";
$endFormat = ($dayTwo) ? Carbon::parse($dayTwo->end)->format('m/d/y h:i a') : "No End Set";
$notes = ($dayTwo) ? Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
    ->title("Schedule Notes")
    ->linkText(($dayTwo->notes) ?: "No Notes")
    ->url("/schedule/$dayTwo->id/notes")->render() : null;
$customerNotes = ($dayTwo) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
    ->title("Schedule Customer Notes")
    ->linkText(($dayTwo->customer_notes) ?: "No Notes")
    ->url("/schedule/$dayTwo->id/customer_notes")->render() : null;

$send = ($dayTwo) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
                                                        ->icon('exclamation')->url("/schedule/$dayTwo->id/send")->render() .
    Button::init()->text("Close Contractor")->color('success btn-xs')
          ->icon('check')->modal('workModal', true)->url("/schedule/$dayTwo->id/close")->render() .
    "</span>" : null;
$startEdit = ($dayTwo) ? "<a class='mjax' data-target='#workModal' href='/schedule/$dayTwo->id/change/start'>$startFormat</a>" : null;
$endEdit = ($dayTwo) ? "<a class='mjax' data-target='#workModal' href='/schedule/$dayTwo->id/change/end'>$endFormat</a>" : null;

$start = ($dayTwo) ? $startEdit : "Set Contractor First";
$end = ($dayTwo) ? $endEdit : "Set Contractor First";
$default = null;
$locked = ($dayTwo) ? $dayTwo->locked ? "<a class='get' href='/schedule/$dayTwo->id/lock'><i class='fa fa-lock'></i></a>" :
    "<a class='get' href='/schedule/$dayTwo->id/lock'><i class='fa fa-unlock'></i></a>" : null;
if ($dayTwo)
{
    $default = $dayTwo && $dayTwo->default_email ?
        Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$dayTwo->id/default")->icon('check')->render() :
        Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$dayTwo->id/default")->icon('times')->render();
}

if ($job->quote->type == 'Full Kitchen' || $job->quote->type == 'Cabinet and Install' || $job->quote->type == 'Builder')
{
    $rows[] = [$color . "<h3>2 {$locked}</h3>", 'Cabinet Installer', $installers . $send, $default, $start, $end, $notes, $customerNotes, null];
}

// Granite installer - day 2.
$granite = JobSchedule::whereJobId($job->id)->whereDesignationId(3)->orderBy('start', 'ASC')->whereAux(false)->first();
$sid = ($granite) ? $granite->id : 0;
$color = ($sid && $granite->sent) ? 'color-success ' : 'color-info ';
$installers = User::whereDesignationId(3)->get();
$opts = [];
foreach ($installers AS $installer)
    $opts[] = ['value' => $installer->id, 'text' => $installer->name];
$installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                      ->title("Select Granite Company")
                      ->linkText(isset($granite->designation_id) && $granite->designation_id ?
                          $granite->user->name : "No Granite Company Assigned")
                      ->source($opts)->url("/job/{$job->id}/schedule/$sid/day/2/designation/3")->render();
$notes = ($granite) ? Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
                              ->title("Schedule Notes")
                              ->linkText(($granite->notes) ?: "No Notes")
                              ->url("/schedule/$granite->id/notes")->render() : null;
$customerNotes = ($granite) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
                                      ->title("Schedule Customer Notes")
                                      ->linkText(($granite->customer_notes) ?: "No Notes")
                                      ->url("/schedule/$granite->id/customer_notes")->render() : null;

$send = ($granite) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
                                                         ->icon('exclamation')->url("/schedule/$granite->id/send")->render() .
    Button::init()->text("Close Contractor")->color('success btn-xs')
          ->icon('check')->modal('workModal', true)->url("/schedule/$granite->id/close")->render() .
    "</span>" : null;
$locked = ($granite) ? $granite->locked ? "<a class='get' href='/schedule/$granite->id/lock'><i class='fa fa-lock'></i></a>" :
    "<a class='get' href='/schedule/$granite->id/lock'><i class='fa fa-unlock'></i></a>" : null;

$startFormat = ($granite) ? Carbon::parse($granite->start)->format('m/d/y h:i a') : "No Start Set";
$endFormat = ($granite) ? Carbon::parse($granite->end)->format('m/d/y h:i a') : "No End Set";
$startEdit = ($granite) ? "<a class='mjax' data-target='#workModal' href='/schedule/$granite->id/change/start'>$startFormat</a>" : null;
$endEdit = ($granite) ? "<a class='mjax' data-target='#workModal' href='/schedule/$granite->id/change/end'>$endFormat</a>" : null;
$start = ($granite) ? $startEdit : "Set Contractor First";
$end = ($granite) ? $endEdit : "Set Contractor First";
$checked = ($granite && $granite->complete) ? "<i class='fa fa-check text-success'></i>" : null;
$default = null;
if ($granite)
{
    $default = $granite && $granite->default_email ?
        Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$granite->id/default")->icon('check')->render() :
        Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$granite->id/default")->icon('times')->render();
}
$cnotes = ($granite) ? $granite->contractor_notes : null;
if ($job->quote->type == 'Full Kitchen' || $job->quote->type == 'Granite Only')
{
    $rows[] = [$color . "<h3>2 $checked {$locked}</h3>", 'Granite Company', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];
}

// Day 4 - Granite again
if ($granite)
{
    $dayFour = JobSchedule::whereJobId($job->id)->whereDesignationId(3)->where('start', '!=', $granite->start)
                          ->whereAux(false)->first();
}
else $dayFour = null;
$sid = ($dayFour) ? $dayFour->id : 0;
$color = ($sid && $dayFour->sent) ? 'color-success ' : 'color-info ';
$installers = User::whereDesignationId(3)->orWhere('id', 60)->get();
$opts = [];
foreach ($installers AS $installer)
    $opts[] = ['value' => $installer->id, 'text' => $installer->name];
$installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                      ->title("Select Granite Company")->linkText(isset($dayFour->designation_id) && $dayFour->designation_id ?
        $dayFour->user->name : "No Granite Company Assigned")
                      ->source($opts)->url("/job/{$job->id}/schedule/$sid/day/4/designation/3")->render();
$startFormat = ($dayFour) ? Carbon::parse($dayFour->start)->format('m/d/y h:i a') : "No Start Set";
$endFormat = ($dayFour) ? Carbon::parse($dayFour->end)->format('m/d/y h:i a') : "No End Set";
$startEdit = ($dayFour) ? "<a class='mjax' data-target='#workModal' href='/schedule/$dayFour->id/change/start'>$startFormat</a>" : null;
$endEdit = ($dayFour) ? "<a class='mjax' data-target='#workModal' href='/schedule/$dayFour->id/change/end'>$endFormat</a>" : null;
$start = ($dayFour) ? $startEdit : "Set Contractor First";
$end = ($dayFour) ? $endEdit : "Set Contractor First";
$locked = ($dayFour) ? $dayFour->locked ? "<a class='get' href='/schedule/$dayFour->id/lock'><i class='fa fa-lock'></i></a>" :
    "<a class='get' href='/schedule/$dayFour->id/lock'><i class='fa fa-unlock'></i></a>" : null;
$send = ($dayFour) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
                                                         ->icon('exclamation')->url("/schedule/$dayFour->id/send")->render() .
    Button::init()->text("Close Contractor")->color('success btn-xs')
          ->icon('check')->modal('workModal', true)->url("/schedule/$dayFour->id/close")->render() .
    "</span>" : null;

$default = null;
if ($dayFour)
{
    $default = $dayFour && $dayFour->default_email ?
        Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$dayFour->id/default")->icon('check')->render() :
        Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$dayFour->id/default")->icon('times')->render();
}

if ($job->quote->type == 'Full Kitchen' || $job->quote->type == 'Granite Only')
{
    $rows[] = [$color . "<h3>4 {$locked}</h3>", 'Granite Company', $installers . $send, $default, $start, $end, null, null, null];
}

// Day 5 - Plumber
$plumber = JobSchedule::whereJobId($job->id)->whereDesignationId(1)->whereAux(false)->first();
$sid = ($plumber) ? $plumber->id : 0;
$color = ($sid && $plumber->sent) ? 'color-success ' : 'color-info ';
$installers = User::whereDesignationId(1)->orWhere('id', 60)->get();
$opts = [];
foreach ($installers AS $installer)
    $opts[] = ['value' => $installer->id, 'text' => $installer->name];
$installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                      ->title("Select Plumber")->linkText(isset($plumber->designation_id) && $plumber->designation_id ?
        $plumber->user->name : "No Plumber Assigned")
                      ->source($opts)->url("/job/$job->id/schedule/$sid/day/5/designation/1")->render();
$send = ($plumber) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
                                                         ->icon('exclamation')->url("/schedule/$plumber->id/send")->render() .
    Button::init()->text("Close Contractor")->color('success btn-xs')
          ->icon('check')->modal('workModal', true)->url("/schedule/$plumber->id/close")->render() .
    "</span>" : null;
$notes = ($plumber) ? Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
                              ->title("Schedule Notes")
                              ->linkText(($plumber->notes) ?: "No Notes")
                              ->url("/schedule/$plumber->id/notes")->render() : null;
$customerNotes = ($plumber) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
                                      ->title("Schedule Customer Notes")
                                      ->linkText(($plumber->customer_notes) ?: "No Notes")
                                      ->url("/schedule/$plumber->id/customer_notes")->render() : null;

$startFormat = ($plumber) ? Carbon::parse($plumber->start)->format('m/d/y h:i a') : "No Start Set";
$endFormat = ($plumber) ? Carbon::parse($plumber->end)->format('m/d/y h:i a') : "No End Set";
$startEdit = ($plumber) ? "<a class='mjax' data-target='#workModal' href='/schedule/$plumber->id/change/start'>$startFormat</a>" : null;
$endEdit = ($plumber) ? "<a class='mjax' data-target='#workModal' href='/schedule/$plumber->id/change/end'>$endFormat</a>" : null;
$start = ($plumber) ? $startEdit : "Set Contractor First";
$end = ($plumber) ? $endEdit : "Set Contractor First";
$checked = ($plumber && $plumber->complete) ? "<i class='fa fa-check text-success'></i>" : null;
$locked = ($plumber) ? $plumber->locked ? "<a class='get' href='/schedule/$plumber->id/lock'><i class='fa fa-lock'></i></a>" :
    "<a class='get' href='/schedule/$plumber->id/lock'><i class='fa fa-unlock'></i></a>" : null;

$default = null;
if ($plumber)
{
    $default = $plumber && $plumber->default_email ?
        Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$plumber->id/default")->icon('check')->render() :
        Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$plumber->id/default")->icon('times')->render();
}
$cnotes = ($plumber) ? $plumber->contractor_notes : null;
if ($job->quote->type == 'Full Kitchen')
{
    $rows[] = [$color . "<h3>5 $checked {$locked}</h3>", 'Plumber', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];
}

// Day 5 - Electrician
$electrician = JobSchedule::whereJobId($job->id)->whereDesignationId(2)->whereAux(false)->first();
$sid = ($electrician) ? $electrician->id : 0;
$color = ($sid && $electrician->sent) ? 'color-success ' : 'color-info ';
$installers = User::whereDesignationId(2)->orWhere('id', 60)->get();
$opts = [];
foreach ($installers AS $installer)
    $opts[] = ['value' => $installer->id, 'text' => $installer->name];
$installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                      ->title("Select Electrician")->linkText(isset($electrician->designation_id) && $electrician->designation_id ?
        $electrician->user->name : "No Electrician Assigned")
                      ->source($opts)->url("/job/$job->id/schedule/$sid/day/5/designation/2")->render();
$send = ($electrician) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
                                                             ->icon('exclamation')->url("/schedule/$electrician->id/send")->render() .
    Button::init()->text("Close Contractor")->color('success btn-xs')
          ->icon('check')->modal('workModal', true)->url("/schedule/$electrician->id/close")->render() .
    "</span>" : null;

$notes = ($electrician) ? Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
                                  ->title("Schedule Notes")
                                  ->linkText(($electrician->notes) ?: "No Notes")
                                  ->url("/schedule/$electrician->id/notes")->render() : null;
$customerNotes = ($electrician) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
                                          ->title("Schedule Customer Notes")
                                          ->linkText(($electrician->customer_notes) ?: "No Notes")
                                          ->url("/schedule/$electrician->id/customer_notes")->render() : null;
$startFormat = ($electrician) ? Carbon::parse($electrician->start)->format('m/d/y h:i a') : "No Start Set";
$endFormat = ($electrician) ? Carbon::parse($electrician->end)->format('m/d/y h:i a') : "No End Set";
$startEdit = ($electrician) ? "<a class='mjax' data-target='#workModal' href='/schedule/$electrician->id/change/start'>$startFormat</a>" : null;
$endEdit = ($electrician) ? "<a class='mjax' data-target='#workModal' href='/schedule/$electrician->id/change/end'>$endFormat</a>" : null;
$start = ($electrician) ? $startEdit : "Set Contractor First";
$end = ($electrician) ? $endEdit : "Set Contractor First";
$locked = ($electrician) ? $electrician->locked ? "<a class='get' href='/schedule/$electrician->id/lock'><i class='fa fa-lock'></i></a>" :
    "<a class='get' href='/schedule/$electrician->id/lock'><i class='fa fa-unlock'></i></a>" : null;

$checked = ($electrician && $electrician->complete) ? "<i class='fa fa-check text-success'></i>" : null;
$default = null;
if ($electrician)
{
    $default = $electrician && $electrician->default_email ?
        Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$electrician->id/default")->icon('check')->render() :
        Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$electrician->id/default")->icon('times')->render();
}
$cnotes = ($electrician) ? $electrician->contractor_notes : null;
if ($job->quote->type == 'Full Kitchen')
{
    $rows[] = [$color . "<h3>5 $checked {$locked}</h3>", 'Electrician', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];
}


// ------------ TILE JOB ------------------ //
if ($job->quote->tiles()->count() > 0) // Got a tile job..
{
    $contractor = JobSchedule::whereJobId($job->id)->whereDesignationId(11)->whereAux(false)->first();
    $sid = ($contractor) ? $contractor->id : 0;
    $color = ($sid && $contractor->sent) ? 'color-success ' : 'color-info ';
    $contractors = User::whereDesignationId(11)->get();
    $opts = [];
    foreach ($contractors AS $c)
        $opts[] = ['value' => $c->id, 'text' => $c->name];
    $contractors = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
        ->title("Select FFT Contractor")->linkText(isset($contractor->designation_id) && $contractor->designation_id ?
            $contractor->user->name : "No Flooring Contractor")
        ->source($opts)->url("/job/$job->id/schedule/$sid/day/6/designation/11")->render();
    $send = ($contractor) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
            ->icon('exclamation')->url("/schedule/$contractor->id/send")->render() .
        Button::init()->text("Close Contractor")->color('success btn-xs')
            ->icon('check')->modal('workModal', true)->url("/schedule/$contractor->id/close")->render() .
        "</span>" : null;

    $notes = ($contractor) ? Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
        ->title("Schedule Notes")
        ->linkText(($contractor->notes) ?: "No Notes")
        ->url("/schedule/$contractor->id/notes")->render() : null;
    $customerNotes = ($contractor) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
        ->title("Schedule Customer Notes")
        ->linkText(($contractor->customer_notes) ?: "No Notes")
        ->url("/schedule/$contractor->id/customer_notes")->render() : null;
    $startFormat = ($contractor) ? Carbon::parse($contractor->start)->format('m/d/y h:i a') : "No Start Set";
    $endFormat = ($contractor) ? Carbon::parse($contractor->end)->format('m/d/y h:i a') : "No End Set";
    $startEdit = ($contractor) ? "<a class='mjax' data-target='#workModal' href='/schedule/$contractor->id/change/start'>$startFormat</a>" : null;
    $endEdit = ($contractor) ? "<a class='mjax' data-target='#workModal' href='/schedule/$contractor->id/change/end'>$endFormat</a>" : null;
    $start = ($contractor) ? $startEdit : "Set Contractor First";
    $end = ($contractor) ? $endEdit : "Set Contractor First";
    $locked = ($contractor) ? $contractor->locked ? "<a class='get' href='/schedule/$contractor->id/lock'><i class='fa fa-lock'></i></a>" :
        "<a class='get' href='/schedule/$contractor->id/lock'><i class='fa fa-unlock'></i></a>" : null;
    $checked = ($contractor && $contractor->complete) ? "<i class='fa fa-check text-success'></i>" : null;
    $default = null;
    if ($contractor)
    {
        $default = $contractor && $contractor->default_email ?
            Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$contractor->id/default")->icon('check')->render() :
            Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$contractor->id/default")->icon('times')->render();
    }
    $cnotes = ($contractor) ? $contractor->contractor_notes : null;
    if ($job->quote->type == 'Full Kitchen')
    {
        $rows[] = [$color . "<h3>6 $checked {$locked}</h3>", 'Flooring Contractor', $contractors. $send, $default, $start, $end, $notes, $customerNotes, $cnotes];
    }
}




// Day 6 - Walkthrough FFT Contractor(5)
$day = $job->quote->tiles()->count() > 0 ? 8 : 7;

$contractor = JobSchedule::whereJobId($job->id)->whereDesignationId(5)->whereAux(false)->first();
$sid = ($contractor) ? $contractor->id : 0;
$color = ($sid && $contractor->sent) ? 'color-success ' : 'color-info ';
$contractors = User::whereDesignationId(5)->get();
$opts = [];
foreach ($contractors AS $c)
    $opts[] = ['value' => $c->id, 'text' => $c->name];
$contractors = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
    ->title("Select FFT Contractor")->linkText(isset($contractor->designation_id) && $contractor->designation_id ?
        $contractor->user->name : "No FFT Contractor Assigned")
    ->source($opts)->url("/job/$job->id/schedule/$sid/day/$day/designation/5")->render();
$send = ($contractor) ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
        ->icon('exclamation')->url("/schedule/$contractor->id/send")->render() .
    Button::init()->text("Close Contractor")->color('success btn-xs')
        ->icon('check')->modal('workModal', true)->url("/schedule/$contractor->id/close")->render() .
    "</span>" : null;

$notes = ($contractor) ? Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
    ->title("Schedule Notes")
    ->linkText(($contractor->notes) ?: "No Notes")
    ->url("/schedule/$contractor->id/notes")->render() : null;
$customerNotes = ($contractor) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
    ->title("Schedule Customer Notes")
    ->linkText(($contractor->customer_notes) ?: "No Notes")
    ->url("/schedule/$contractor->id/customer_notes")->render() : null;
$startFormat = ($contractor) ? Carbon::parse($contractor->start)->format('m/d/y h:i a') : "No Start Set";
$endFormat = ($contractor) ? Carbon::parse($contractor->end)->format('m/d/y h:i a') : "No End Set";
$startEdit = ($contractor) ? "<a class='mjax' data-target='#workModal' href='/schedule/$contractor->id/change/start'>$startFormat</a>" : null;
$endEdit = ($contractor) ? "<a class='mjax' data-target='#workModal' href='/schedule/$contractor->id/change/end'>$endFormat</a>" : null;
$start = ($contractor) ? $startEdit : "Set Contractor First";
$end = ($contractor) ? $endEdit : "Set Contractor First";
$locked = ($contractor) ? $contractor->locked ? "<a class='get' href='/schedule/$contractor->id/lock'><i class='fa fa-lock'></i></a>" :
    "<a class='get' href='/schedule/$contractor->id/lock'><i class='fa fa-unlock'></i></a>" : null;

$checked = ($contractor && $contractor->complete) ? "<i class='fa fa-check text-success'></i>" : null;
$default = null;
if ($contractor)
{
    $default = $contractor && $contractor->default_email ?
        Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$contractor->id/default")->icon('check')->render() :
        Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$contractor->id/default")->icon('times')->render();
}
$cnotes = ($contractor) ? $contractor->contractor_notes : null;
if ($job->quote->type == 'Full Kitchen')
{
    $rows[] = [$color . "<h3>$day $checked {$locked}</h3>", 'FFT Contractor', $contractors. $send, $default, $start, $end, $notes, $customerNotes, $cnotes];
}













// #20 - Auxillary schedule days.
$auxs = JobSchedule::whereJobId($job->id)->whereAux(true)->get();
$everyone = User::orderBy('name', 'ASC')->get();
$opts = [];
foreach ($everyone AS $errbody)
    $opts[] = ['value' => $errbody->id, 'text' => $errbody->name];
foreach ($auxs AS $aux)
{
    $contractors = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                           ->title("Select Contractor")->linkText(($aux->user) ? $aux->user->name : "No Contractor Assigned")
                           ->source($opts)->url("/schedule/$aux->id/contractor")->render();
    $color = ($aux->sent) ? 'color-success ' : 'color-info ';
    $send = ($aux->start != '0000-00-00 00:00:00') ? "<span class='pull-right'>" . Button::init()->text("Send Notification")->color('warning get btn-xs')
                                                                                         ->icon('exclamation')->url("/schedule/$aux->id/send")->render() .
        Button::init()->text("Close Contractor")->color('success btn-xs')
              ->icon('check')->modal('workModal', true)->url("/schedule/$aux->id/close")->render() .
        "</span>" : null;
    $trash = "<span class='pull-right'><a class='get' href='/schedule/$aux->id/delete'><i class='fa fa-trash-o'></i></a></span>";
    $notes = Editable::init()->id("idNo_$job->id")->placement('right')->type('textarea')
                     ->title("Schedule Notes")
                     ->linkText(($aux->notes) ?: "No Notes")
                     ->url("/schedule/$aux->id/notes")->render();
    $customerNotes = ($aux) ? Editable::init()->id("idNo_$job->id")->placement('left')->type('textarea')
                                      ->title("Schedule Customer Notes")
                                      ->linkText(($aux->customer_notes) ?: "No Notes")
                                      ->url("/schedule/$aux->id/customer_notes")->render() : null;
    $startFormat = ($aux->start != '0000-00-00 00:00:00') ? Carbon::parse($aux->start)->format('m/d/y h:i a') : "No Start Set";
    $endFormat = ($aux->end != '0000-00-00 00:00:00') ? Carbon::parse($aux->end)->format('m/d/y h:i a') : "No End Set";
    $startEdit = ($aux) ? "<a class='mjax' data-target='#workModal' href='/schedule/$aux->id/change/start'>$startFormat</a>" : null;
    $endEdit = ($aux) ? "<a class='mjax' data-target='#workModal' href='/schedule/$aux->id/change/end'>$endFormat</a>" : null;
    $start = ($aux) ? $startEdit : "Set Contractor First";
    $end = ($aux) ? $endEdit : "Set Contractor First";
    $checked = ($aux && $aux->complete) ? "<i class='fa fa-check text-success'></i>" : null;
    $default = null;
    $locked = ($aux) ? $aux->locked ? "<a class='get' href='/schedule/$aux->id/lock'><i class='fa fa-lock'></i></a>" :
        "<a class='get' href='/schedule/$aux->id/lock'><i class='fa fa-unlock'></i></a>" : null;

    if ($aux)
    {
        $default = $aux && $aux->default_email ?
            Button::init()->text("Default Enabled")->color('success get')->url("/schedule/$aux->id/default")->icon('check')->render() :
            Button::init()->text("Default Disabled")->color('danger get')->url("/schedule/$aux->id/default")->icon('times')->render();
    }
    $cnotes = ($aux) ? $aux->contractor_notes : null;
    $rows[] = [$color . " $trash $checked $locked", ($aux->designation) ? $aux->designation->name : "No Designation", $contractors . $send, $default, $start,
        $end, $notes, $customerNotes, $cnotes];
}


$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(12, $table);
echo BS::row($span);
$span = BS::span(12, "<br/><br/><div id='calendar'></div>");

echo BS::encap("
  $('#calendar').fullCalendar({
                allDayDefault : false,
                header: {
                    left: 'prev,next today,title',
                    right: 'month,agendaWeek,agendaDay'
                },

              });");


echo BS::row($span);

// Buttons
if (Auth::user()->manager || Auth::user()->superuser)
{
    $close = Button::init()->text("Close Job (Send to FFT)")->color('info get')->url("/job/$job->id/close")
                   ->icon('arrow-right')->render();
    $add = Button::init()->text("Add Additional Schedule")->color('primary get')->url("/job/$job->id/schedules/new")->icon('plus')->render();
    $add .= Button::init()->text("See Schedules")->click("location.reload()")->color('info')->icon('refresh')->render();
    $color = ($job->schedules_sent) ? "success" : "danger";
    $customer = Button::init()->text("Send Schedule to Customer")->color($color)->url("/job/$job->id/sendSchedules")
                      ->icon('check')->render();
    echo BS::row(BS::span(12, $close . $add . "<span class='pull-right'>$customer</span>"));
}
echo Modal::init()->id('workModal')->onlyConstruct()->render();
