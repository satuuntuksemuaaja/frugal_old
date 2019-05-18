<?php
$fields = [];
$fields[] = ['type' => 'textarea', 'var' => 'note', 'text' => 'Enter Note', 'span' => 7];
$form = Forms::init()->id('closeForm')->labelSpan(4)->elements($fields)->url("/job/$job->id/buildupnote")->render();
$save = Button::init()->text("Save Note")->color('primary mpost')->formid('closeForm')->icon('check')->render();
echo Modal::init()->isInline()->header("Add Buildup Note")->content($form)->footer($save)->render();
