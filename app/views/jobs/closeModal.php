<?php
$fields = [];
$fields[] = ['type' => 'textarea', 'var' => 'notes', 'text' => 'Contractor Notes (if any)', 'span' => 7];
$form = Forms::init()->id('closeForm')->labelSpan(4)->elements($fields)->url("/schedule/$schedule->id/contractor/close")->render();
$save = Button::init()->text("Close Schedule")->color('primary mpost')->formid('closeForm')->icon('check')->render();
echo Modal::init()->isInline()->header("Close Contractor Schedule")->content($form)->footer($save)->render();
