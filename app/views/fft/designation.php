<?php
$designations = Designation::whereActive(true)->orderBy('name')->get();
$opts = [];
if ($item->designation)
    $opts[] = ['val' => $item->designation_id, 'text' => $item->designation->name];

foreach ($designations as $d)
{
    $opts[] = ['val' => $d->id, 'text' => $d->name];
}

$fields[] = ['type' => 'select',  'var' => 'designation_id', 'opts' => $opts, 'text' => "Select Designation:", 'span' => 7];
$form = Forms::init()->id('notesForm')->url("/fft_designation/$item->id")->elements($fields)->render();
$save = Button::init()->text("Save Designation")->color('primary mpost')->formid('notesForm')->icon('check')->render();
echo Modal::init()->isInline()->header("Punch Item Designation")->content($form)->footer($save)->render();