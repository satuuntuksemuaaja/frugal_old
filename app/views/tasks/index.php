<?php
use Carbon\Carbon;
echo BS::title("Tasks", "My Tasks");
$admin = (Auth::user()->superuser || Auth::user()->manager) ? true : false;

 $buttons[] = ['icon' => 'plus', 'color' => 'primary', 'text' => 'Create Task', 'modal' => 'newTask'];
  echo BS::row(BS::span(12, BS::Buttons($buttons)));
$tasks = ($admin) ? Task::whereClosed(false)->get() :
  Task::whereClosed(false)->whereAssignedId(Auth::user()->id)->get();
if ($tasks->count() == 0 && !$admin)
  {
    echo BS::callout('info', '<b>Great!</b> You have no pending tasks.');

  }
$headers = ['ID', 'Task', 'Assigned', 'Customer', 'Job', 'Created', 'Created By', 'Last Updated', 'Due'];
$rows = [];
foreach ($tasks AS $task)
{
  $color = ($task->urgent && !$task->satisfied) ? 'color-danger ' : null;

  if ($task->job)
  {
    $customer = $task->job->quote->lead->customer->name;
    $job = "#{$task->job->id} - {$task->job->quote->type}";
  }
  else if ($task->customer)
  {
    $customer = $task->customer->name;
    $job =  "No Job";
  }
  else
  {
    $customer = "No Customer";
    $job = "No Job";
  }

  $assigned = ($task->assigned) ? $task->assigned->name : "Unassigned";
  $due = ($task->due != '0000-00-00') ? Carbon::parse($task->due)->format('m/d/y h:i a') : "No Due Date";
  $rows[] = [$color.$task->id,
            "<a href='/task/$task->id/view'>$task->subject</a>",
            $assigned,
            $customer,
            $job,
            $task->created_at->format('m/d/y h:i a'),
            $task->user->name,
            $task->updated_at->format('m/d/y h:i a'),
            $due];

}
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(12, $table);
echo BS::row($span);


// New task modal
$pre = BS::callout('info', "<b>Selecting Fields</b> You do not need to select a customer and a job, just one or the other.
  If this task is generic and not related to a customer or job, just leave them blank. If there is no due date, leave blank.");
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Subject:', 'var' => 'subject', 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Due Date:', 'mask' => '99/99/99', 'var' => 'due_date', 'span' => 5, 'class' => 'dp'];
$fields[] = ['type' => 'select', 'var' => 'due_time', 'text' => 'Due Time:', 'opts' => \vl\core\Formatter::getTimes(), 'span' => 3];
$opts = [];
$opts[] = ['val' => 'Y', 'text' => null, 'checked' => false];
$fields[] = ['type' => 'checkbox', 'var' => 'urgent', 'text' => 'Is this urgent?', 'opts' => $opts, 'span' => 5];
$users = User::orderBy('name', 'ASC')->get();
if (Auth::user()->superuser || Auth::user()->manager)
{
  $opts = [];
  $opts[] = ['val' => 0, 'text' => '--- Unassigned ---'];
  foreach ($users AS $user)
    $opts[] = ['val' => $user->id, 'text' => $user->name];
  $fields[] = ['type' => 'select', 'var' => 'assigned', 'opts' => $opts, 'span' => 6, 'text' => "Assigned:"];
}
else
  $fields[] = ['type' => 'hidden', 'var' => 'assigned', 'val' => Auth::user()->id];
$customers = Customer::orderBy('name', 'ASC')->get();

$opts = [];
$opts[] = ['val' => 0, 'text' => '--- No Customer ---'];
foreach ($customers AS $customer)
  $opts[] = ['val' => $customer->id, 'text' => $customer->name];
$fields[] = ['type' => 'select2', 'var' => 'customer_id', 'opts' => $opts, 'span' => 6, 'text' => 'Customer:'];
$opts = [];
$opts[] = ['val' => 0, 'text' => '--- No Job ---'];
$jobs = Job::whereClosed(false)->get();
foreach ($jobs AS $job)
  if ($job->quote && $job->quote->lead)
    $opts[] = ['val' => $job->id, 'text' => $job->quote->lead->customer->name . " ({$job->quote->type} - $job->id)"];
$fields[] = ['type' => 'select2', 'var' => 'job_id', 'opts' => $opts, 'span' => 6, 'text' => 'Job:', 'width' => 400];
$fields[] = ['type' => 'textarea', 'var' => 'body', 'span' => 7, 'text' => 'Details:'];
$form = Forms::init()->id('newTaskForm')->labelSpan(4)->url("/tasks/new")->elements($fields)->render();
$create = Button::init()->text("Create Task")->icon('plus')->color('primary mpost')->formid('newTaskForm')->render();
echo Modal::init()->id('newTask')->header("Create Task")->content($pre.$form)->footer($create)->render();
echo BS::encap("
$('.dp').datepicker({format: 'mm/dd/yyyy'});");








