<?php
// New task modal
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Subject:', 'var' => 'subject', 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Due Date:', 'mask' => '99/99/99', 'var' => 'due_date', 'span' => 5];
$fields[] = ['type' => 'select', 'var' => 'due_time', 'text' => 'Due Time:', 'opts' => \vl\core\Formatter::getTimes(), 'span' => 3];
$opts = [];
$opts[] = ['val' => 'Y', 'text' => null, 'checked' => false];
$fields[] = ['type' => 'checkbox', 'var' => 'urgent', 'text' => 'Is this urgent?', 'opts' => $opts, 'span' => 5];
$users = User::orderBy('name', 'ASC')->get();
$opts = [];
$opts[] = ['val' => 0, 'text' => '--- Unassigned ---'];
foreach ($users AS $user)
  $opts[] = ['val' => $user->id, 'text' => $user->name];
$fields[] = ['type' => 'select', 'var' => 'assigned', 'opts' => $opts, 'span' => 6, 'text' => "Assigned:"];
$fields[] = ['type' => 'hidden', 'var' => 'customer_id', 'val' => (isset($customer) ? $customer->id : null)];
$opts = [];
$opts[] = ['val' => 0, 'text' => '--- No Job ---'];
$fields[] = ['type' => 'hidden', 'var' => 'job_id', 'val' => (isset($job) ? $job->id : null)];
$fields[] = ['type' => 'textarea', 'var' => 'body', 'span' => 7, 'text' => 'Details:'];
if (Input::has('fromProfile'))
  $fields[] = ['type' => 'hidden', 'var' => 'fromProfile', 'val' => 'true'];
$form = Forms::init()->id('newTaskForm')->labelSpan(4)->url("/tasks/new")->elements($fields)->render();
$create = Button::init()->text("Create Task")->icon('plus')->color('primary mpost')->formid('newTaskForm')->render();
echo Modal::init()->isInline()->header("Create Task")->content($form)->footer($create)->render();