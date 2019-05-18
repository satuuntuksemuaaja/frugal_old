<?php
use Carbon\Carbon;
use vl\core\Google;
class TasksController extends BaseController
{
  public $layout = "layouts.main";

  public function index()
  {
    $view = View::make('tasks.index');
    $this->layout->title = "Tasks";
    $this->layout->content = $view;
  }

  public function createTask()
  {
    if (!Input::has('subject') || !Input::has('body'))
      return Response::json(['status' => 'danger', 'gtitle' => 'Unable to Create', 'gbody' => 'Need to have a subject
        and a body.']);
    $task = new Task;
    $task->user_id = Auth::user()->id;
    $task->assigned_id = (Input::get('assigned')) ?: 0;
    $task->subject = Input::get('subject');
    $task->body = Input::get('body');
    $task->job_id = (Input::get('job_id')) ?: 0;
    $task->customer_id = (Input::get('customer_id')) ?: 0;
    $task->urgent = Input::has('urgent') ? 1 : 0;
    $task->closed = 0;
    if (Input::has('due_date'))
    {
        $date = explode("/", Input::get('due_date')); // omg..
       // if ($date[0] < 10) $date[0] = "0{$date[0]}";
       // if ($date[1] < 10) $date[1] = "0{$date[1]}";
        $date = implode("/", $date);
        $date = Carbon::createFromFormat("m/d/y", $date);
        $time = explode(":", Input::get('due_time'));
        $date->setTime($time[0], $time[1], 0);
        $task->due = $date;
    }
    $task->save();
    if ($task->assigned_id && $task->assigned_id != Auth::user()->id)
    {
      $user = User::find($task->assigned_id);
      $user->task_id = $task->id;
      $user->save();
      $customer = ($task->customer) ? "(".$task->customer->name.")" : null;
      $urgentMessage = ($task->urgent) ? "** URGENT ** (Reply TC or LM when Complete) - " : null;
      \vl\core\SMS::command('directory.send',
                ['target' => $user->mobile,
                'message' => "($task->id) $customer {$urgentMessage} New Task: $task->subject : $task->body"]);
    }
    // Create a Google Calendar Event
    try
    {
      if (Input::has('due_date'))
      {
        $params = [];
        $params['title'] = $task->subject;
        $params['location'] = "Task #{$task->id} in frugalk.com";
        $params['description'] = $task->body;
        $params['start'] = Carbon::parse($task->due);
        $params['end'] = Carbon::parse($task->due)->addMinutes(30);
        Google::event(User::find($task->assigned_id), $params);
      }
    }
    catch (Exception $e)
    {

    }
    if (Input::has('fromProfile'))
      return Response::json(['status' => 'success', 'action' => 'reload', 'url' => "/profile/$task->customer_id/view"]);
    return Response::json(['status' => 'success', 'action' => 'selfreload']);
  }

  public function view($id)
  {
    $view = View::make('tasks.view');
    $view->task = Task::find($id);
    $this->layout->title = "Tasks :: " . $view->task->subject;
    $this->layout->content = $view;
    $this->layout->crumbs = [
        ['url' => '/tasks', 'text' => 'Tasks'],
        ['url' => '#', 'text' => $view->task->subject]
       ];
  }
  public function closeTask($id)
  {
    $task = Task::find($id);
    $task->closed = 1;
    $task->satisfied = 1;
    $note = new TaskNote;
    $note->task_id = $task->id;
    $note->user_id = Auth::user()->id;
    $note->body = "<h4>Task marked complete</h4>";
    $note->save();
    $task->save();
    return Response::json(['status' => 'success', 'action' => 'reload', 'url' => '/tasks']);
  }

  public function createNote($id)
  {
    if (!Input::has('body'))
      return Response::json(['status' => 'danger', 'gtitle' => 'Unable to Create',
        'gbody' => 'Please enter a note before saving']);
    $task = Task::find($id);
    $note = new TaskNote;
    $note->task_id = $task->id;
    $note->user_id = Auth::user()->id;
    $note->body = Input::get('body');
    $note->save();
    $task->save();
    if ($task->assigned_id)
    {
      $user = User::find($task->assigned_id);
      $user->task_id = $task->id;
      $user->save();
      \vl\core\SMS::command('directory.send',
                ['target' => $user->mobile,
                'message' => "($task->id) Update: $note->body"]);
    }
    return Response::json(['status' => 'success', 'action' => 'selfreload']);
  }

  public function quickModal($cid, $jid)
  {
    $view = View::make('tasks.customerModal');
    if ($jid)
      $view->job = Job::find($jid);
    if ($cid)
      $view->customer = Customer::find($cid);
    return $view;
  }

}