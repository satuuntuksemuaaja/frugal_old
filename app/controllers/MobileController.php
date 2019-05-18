<?php
class MobileController extends BaseController
{
  public $layout = "layouts.main";

  public function index()
  {
    $view = View::make('mobile.index');
    $this->layout->title = "Frugal Kitchens Mobile";
    $this->layout->content = $view;
  }

  public function view($id)
  {
    $view = View::make('mobile.view');
    $view->schedule = JobSchedule::find($id);
    $this->layout->title = "Frugal Kitchens Mobile";
    $this->layout->content = $view;
  }

  public function update($id)
  {
    $schedule = JobSchedule::find($id);
    $schedule->contractor_notes = Input::get('notes');
    if (!$schedule->contractor_notes) $schedule->contractor_notes = "Job has been completed";
    if ($schedule->job->quote->lead->customer)
    {
      $out['content'] = Auth::user()->name . " just added the following note to " .
      $schedule->job->quote->lead->customer->name . "'s Job: <p>".nl2br($schedule->contractor_notes)."</p>";
      $to = "Contractor Notes";
      $email = "contractornotes@frugalkitchens.com";
      $subject = "New Note for Job: #{$schedule->job->id} - {$schedule->job->quote->type} for {$schedule->job->quote->lead->customer->name}";
      Mail::send('emails.notification', $out, function($message) use ($email, $to, $subject)
        {
          $message->to($email, $to)->subject($subject);
        });
    }
    $schedule->save();
    if (Input::has('complete'))
      return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/mobile/job/{$schedule->job->id}/punch"]);
    else
      return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/mobile"]);
  }

  public function punch($id)
  {
    $view = View::make('mobile.punch');
    $view->job = Job::find($id);
    $this->layout->title = "Frugal Kitchens Mobile";
    $this->layout->content = $view;
  }

  public function punchSave($id)
  {
    $job = Job::find($id);
    $ok = true;
    foreach (Input::all() AS $key => $val)
    {

      if (preg_match('/p_/', $key))
      {
        $key = str_replace("p_", null, $key);
        $answer = PunchAnswer::wherePunchId($key)->whereUserId(Auth::user()->id)->first();
        if (!$answer) $answer = new PunchAnswer;
        $answer->job_id = $job->id;
        $answer->punch_id = $key;
        $answer->answer = $val;
        $answer->save();
        if ($val != 'Yes') $ok = false;
      } // if a key
    } // fe input
    if ($ok)
    {
      foreach ($job->schedules AS $schedule)
      {
        if ($schedule->designation_id == Auth::user()->designation_id)
        {
          $schedule->complete = 1;
          $schedule->save();
        } // if our schedule
      } // fe job schedule
    // Check to see if we need to close out the job.
    \vl\jobs\JobBoard::checkSchedulesForClosing($schedule->job);

    return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/mobile"]);
    } // we ok?
    else
    {
      return Response::json(['status' => 'danger', 'gtitle' => 'Unable to Close', 'gbody' => 'All answers must be yes
        to close out the job.']);
    }
  } // fn

}