<?php
$headers = ['Time', 'Notes', 'User'];
$rows = [];
foreach ($job->notes AS $note)
{
    $rows[] = [$note->created_at->format("m/d/y h:ia"), nl2br($note->note), $note->user ? $note->user->name : "Unknown"];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$fields[] = ['type' => 'textarea', 'rows' => 10, 'span' => 9, 'var' => 'note'];
$form = Forms::init()->id('notesForm')->url("/job/$job->id/notes")->elements($fields)->render();
$save = Button::init()->text("Save Notes")->color('primary mpost')->formid('notesForm')->icon('check')->render();
echo Modal::init()->isInline()->header("Job Notes")->content($table.$form)->footer($save)->render();