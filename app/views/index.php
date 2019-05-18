<?php
use vl\core\CalendarEngine;

$buttons = null;
// Types
if (!Auth::user()->superuser && !Auth::user()->manager && !Auth::user()->level_id == 10)
{
 $events = CalendarEngine::byUser(Auth::user()->id);
 echo BS::span(12, "<br/><br/><div id='calendar'></div>");

  echo BS::encap("
  $('#calendar').fullCalendar({
                allDayDefault : false,
                header: {
                    left: 'prev,next today,title',
                    right: 'month,agendaWeek,agendaDay'
                },
                {$events}

              });");

  return;
}

$items[] = ['url' => "/?category=appointments", 'text' => 'All Appointments', 'icon' => 'clock-o'];
$items[] = ['url' => "/?category=appointments&location=fayetteville", 'text' => 'Fayetteville Appointments', 'icon' => 'clock-o'];
$items[] = ['url' => "/?category=appointments&location=roswell", 'text' => 'Roswell Appointments', 'icon' => 'clock-o'];
$items[] = ['url' => "/?category=appointments&location=toco hills", 'text' => 'Toco Hill Appointments', 'icon' => 'clock-o'];
$items[] = ['url' => "/?category=appointments&location=peachtree city", 'text' => 'PTC Appointments', 'icon' => 'clock-o'];

$items[] = ['url' => "/?category=jobs", 'text' => 'Jobs', 'icon' => 'cogs'];
$text = (Input::has('category')) ? "By " . Input::get('location') . " " .Input::get('category') : "Show by Category";

$buttons .= Button::init()->text($text)->dropdown($items)->color('primary')->icon('search')->url('#')->render();

// Users
$items = [];
$users = User::orderBy('name', 'ASC')->get();
foreach ($users AS $user)
  $items[] = ['url' => "/?user=$user->id", 'text' => $user->name, 'icon' => 'user'];
$text = (Input::has('user')) ? User::find(Input::get('user'))->name : "Show by User";
$buttons .= Button::init()->text($text)->dropdown($items)->color('info')->icon('user')->url('#')->render();

// Designation
$items = [];
$designations = Designation::orderBy('name', 'ASC')->get();
foreach ($designations AS $designation)
  $items[] = ['url' => "/?designation=$designation->id", 'text' => $designation->name, 'icon' => 'users'];
$text = (Input::has('designation')) ? Designation::find(Input::get('designation'))->name : "Show by Designation";
$buttons .= Button::init()->text($text)->dropdown($items)->color('warning')->icon('users')->url('#')->render();



$span = BS::span(6, $buttons);
echo BS::row($span);
echo BS::span(12, "<br/><br/><div id='calendar'></div>");



// Event Gathering
if (Input::get('category') == 'appointments')
  $events = CalendarEngine::byAppointments(Input::get('location'));
else if (Input::get('category') == 'jobs')
  $events = CalendarEngine::byJobs();
else if (Input::get('designation'))
  $events = CalendarEngine::byDesignation(Input::get('designation'));
else if (Input::get('user'))
  $events = CalendarEngine::byUser(Input::get('user'));
else
{
    if (Auth::user()->id == 5)
        $events = CalendarEngine::byAppointments();
    else
    $events = CalendarEngine::byUser(Auth::user()->id);
}



echo BS::encap("

  $('#calendar').fullCalendar({
                allDayDefault : false,
                header: {
                    left: 'prev,next today,title',
                    right: 'month,agendaWeek,agendaDay'
                },
                {$events}

              });");
