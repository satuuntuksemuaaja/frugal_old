<?php
$headers = ['Time', 'Notes', 'User'];
$rows = [];
foreach ($fft->thread_notes()->get() AS $note)
{
    if (trim($note->note))
    $rows[] = [$note->created_at->format("m/d/y h:ia"), nl2br($note->note), $note->user ? $note->user->name : "From Import"];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$fields[] = ['type' => 'textarea', 'rows' => 10, 'span' => 9, 'var' => 'note'];
$form = Forms::init()->id('notesForm')->url("/fft/$fft->id/notes")->elements($fields)->render();
$save = Button::init()->text("Save Notes")->color('primary mpost')->formid('notesForm')->icon('check')->render();
echo Modal::init()->isInline()->header("FFT Payment Notes")->content($table.$form)->footer($save)->render();