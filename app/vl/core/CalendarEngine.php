<?php
namespace vl\core;
use Auth;
use \Carbon\Carbon;
use User, Designation, Showroom, Closing, Measure;
use Job, JobSchedule, FFT, Task;
class CalendarEngine
{

  const FORMAT = "events: [%s],";

  static public function byUser($user)
  {
      if (Auth::user()->level_id == 4)
          $user = Auth::user()->id;
    $events = [];
    $userObj = User::find($user);
    switch ($userObj->designation_id)
    {
      case 1: $schedules = JobSchedule::whereUserId($user)->get(); break;
      case 2: $schedules = JobSchedule::whereUserId($user)->get(); break;
      case 3: $schedules = JobSchedule::whereUserId($user)->get(); break;
      case 4: $schedules = JobSchedule::whereUserId($user)->get(); break;
      case 5: $schedules = FFT::whereUserId($user)->get(); break;
      default : null;
    }
    if (isset($schedules))
    {
      foreach ($schedules AS $schedule)
      {
        if ($schedule->job && $schedule->job->quote && $schedule->user)
        $events[] = [
          'title' => $schedule->user->name . " for " .
             $schedule->job->quote->lead->customer->name,
          'time' => ($userObj->designation_id == 5) ? $schedule->schedule_start : $schedule->start,
          'color' => $schedule->user->color,
          'url' => "/job/{$schedule->job->id}/schedules"
        ];
      } // fe
      // #30 - Add tasks to calendar
    } // if scheules
    else
      $events = self::byAppointments(null, $user);
    $tasks = Task::whereAssignedId($user)->get();
      foreach ($tasks AS $task)
      {
        if ($task->due != '0000-00-00')
        $events[] = [
          'title' => $task->subject,
          'time' => $task->due,
          'color' => $task->user->color,
          'url' => "/task/{$task->id}/view"
        ];
      }
    return self::render($events);

  }

  static public function render(Array $events)
  {
    $data = null;
    foreach ($events AS $event)
    {
      //$time = strtotime($event['time']);
      $time = Carbon::parse($event['time'])->format("m/d/y H:i");
      $end = Carbon::parse($event['time'])->addMinutes(30)->format("m/d/y H:i");
      $title = $event['title'];
      $title = str_replace("'", null, $title);
      $title = str_replace('"', null, $title);
      $url = (isset($event['url'])) ? "url: '$event[url]', " : null;
      if (!isset($event['color']))
        $event['color'] = '#5bc0de';
      else $event['color'] = '#' . $event['color'];
      $data .= "{
                  title: '$title',
                  start: '{$time}:00',
                  end: '{$end}:00',
                  backgroundColor: '$event[color]',
                  {$url}
                },

                ";
    }
    return sprintf(self::FORMAT, $data);
  }

  static public function byDesignation ($designation)
  {
      if (Auth::user()->level_id == 4)
          return null;
    $events = [];
    if ($designation == 5 || $designation == 22) // Get FFTs
    $schedules = FFT::all();
    else
    $schedules = JobSchedule::whereDesignationId($designation)->get();
    foreach ($schedules AS $schedule)
      {
        if ($schedule->job && $schedule->job->quote && $schedule->user)
        $events[] = [
          'title' => $schedule->user->name . " for " .
             $schedule->job->quote->lead->customer->name,
          'time' => ($designation == 5) ? $schedule->schedule_start : $schedule->start,
          'color' => $schedule->user->color,
          'url' => "/job/{$schedule->job->id}/schedules"
        ];
      } // fe
    return self::render($events);
  }

  static public function byAppointments($location = null, $user = null)
  {
    $start = Carbon::now()->startOfMonth();
    $showrooms = (new Showroom)->where('scheduled', '>=', $start);
    if ($location) $showrooms = $showrooms->whereLocation($location);
    $showrooms = $showrooms->get();

    $closings = Closing::where('scheduled', '>=', $start)->get();

    $measures = Measure::where('scheduled', '>=', $start)->get();
    $events = [];
    foreach ($showrooms AS $showroom)
      {
        if (!$showroom->lead) continue;
        if (!$showroom->lead->user) continue;
        if ($user && $showroom->lead->user_id != $user) continue;
        $events[] = [
        'title' => "(S) ".$showroom->lead->customer->name . " in ". $showroom->location,
        'time' => $showroom->scheduled,
        'color' => $showroom->lead->user->color,
        'url' => "/profile/{$showroom->lead->customer->id}/view"
        ];
      }
    foreach ($closings AS $closing)
      {
        if (!$closing->lead->user) continue;
        if ($user && $closing->lead->user_id != $user) continue;
        $events[] = [
        'title' => "(C) ".$closing->lead->customer->name,
        'time' => $closing->scheduled,
        'color' => $closing->lead->user->color,
        'url' => "/profile/{$closing->lead->customer->id}/view"];
      }
     foreach ($measures AS $measure)
      {
        if (!$measure->lead) continue;
        if (!$measure->lead->user) continue;
        if ($user && $measure->lead->user_id != $user) continue;
        $events[] = [
        'title' => "(M) ".$measure->lead->customer->name,
        'time' => $measure->scheduled,
        'color' => $measure->lead->user->color,
        'url' => "/profile/{$measure->lead->customer->id}/view"];
      }
    if ($user) return $events;
    return self::render($events);
  }


  static public function byJobs($jobs = null)
  {
    $events = [];
    if (!$jobs)
      $jobs = Job::whereClosed(false)->get();
    foreach ($jobs AS $job)
    {
      foreach ($job->schedules AS $schedule)
      {
          if (Auth::user()->level_id == 4 && $schedule->user->id != Auth::user()->id)
              continue;

              $events[] = [
          'title' => $schedule->user->name . " for " . $job->quote->lead->customer->name,
          'time' => $schedule->start,
          'color' => $schedule->user->color,
          'url' => "/job/{$job->id}/schedules"
        ];
      } // fe
    } //fe
    return self::render($events);

  } //class


}