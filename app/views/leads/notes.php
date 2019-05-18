<?php
$headers = ['Timestamp', 'From', 'Note'];
$rows = [];
foreach ($lead->notes AS $note)
{
    $rows[] = [$note->created_at->format("m/d/y h:i a"), $note->user->name, $note->note];
}
$table = Table::init()->headers($headers)->rows($rows)->render();

$fields = [];
$fields[] = ['type' => 'textarea', 'var' => 'notes', 'text' => 'Notes:', 'span' => 7];
$form = Forms::init()->id('notesForm')->elements($fields)->url("/lead/$lead->id/notes")->render();
$save = Button::init()->text("Add")->color('primary mpost')->formid('notesForm')->icon('plus')->render();
echo Modal::init()->isInline()->header("Lead Notes")->content($table . $form)->footer($save)->render();