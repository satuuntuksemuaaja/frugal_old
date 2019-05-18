<?php
use Carbon\Carbon;
$customer = ($task->customer) ? " (<a href='/profile/{$task->customer->id}/view'>".$task->customer->name."</a>)" : null;
echo BS::title("Tasks", $task->subject . $customer);

$due = ($task->due != '0000-00-00') ? "<b>* This task is due on " . Carbon::parse($task->due)->format('m/d/y h:i a') : null;
$left = "<div class='well'><p class='lead'>".nl2br($task->body)."</p>{$due}</div>";

$fields = [];
$fields[] = ['type' => 'textarea', 'var' => 'body', 'text' => 'Add Note:', 'span' => 8];
$form = Forms::init()->id('newNoteForm')->labelSpan(3)->url("/task/$task->id/note/create")->elements($fields)->render();
$save = Button::init()->text("Add Note")->icon('plus')->color('primary post')->formid('newNoteForm')->render();
$close = Button::init()->text("Close Task")->color('danger get')->icon('trash-o')->url("/task/$task->id/close")->render();
$panel = Panel::init('primary')->header("New Note")->content($form)->footer($save.$close)->render();
$left .= $panel;

// Notes
foreach ($task->notes()->orderBy('created_at', 'DESC')->get() AS $note)
{
  $left .= Panel::init()->content("<p>".nl2br($note->body)."</p>")->footer("Added by " . $note->user->name . " on " .
      $note->created_at->format("m/d/y h:i a"))->render();
}


$left = BS::span(6, $left);
echo BS::row($left);
