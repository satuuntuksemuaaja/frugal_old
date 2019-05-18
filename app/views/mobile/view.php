<?php
echo BS::title("Frugal Mobile");
use Carbon\Carbon;
// Add notes to job.
// Also add Complete job, and Job not complete buttons
$fields = [];
$fields[] = ['type' => 'textarea', 'var' => 'notes', 'val' => $schedule->notes, 'span' => 8, 'text' => 'Notes:'];
$form = Forms::init()->id('notesForm')->url("/mobile/schedule/$schedule->id/update")->elements($fields)->render();
$span = BS::span(12, $form);
echo BS::row($span);
if (!$schedule->complete)
{
  $close = Button::init()->text("Mark Job Complete")->color('success post')->formid('notesForm')->icon('check')
  ->postVar('complete')->id('true')->render();
  $unfinished = Button::init()->text("Job Incomplete")->color('warning post')->formid('notesForm')
  ->icon('exclamation')->render();
  $span = BS::span(12,$close."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;". $unfinished);
  echo BS::row($span);
}

