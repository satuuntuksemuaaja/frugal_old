<?php
$pre = BS::callout('info', "You are duplicating a quote in it's exact form. You will need to change the quote title to
  differentiate between the original(s) and duplicates.");
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Duplicated Quote Title:', 'var' => 'title', 'span' => 7];
$form = Forms::init()->id('duplicateForm')->labelSpan(4)->url("/quote/$quote->id/duplicate")->elements($fields)->render();
$save = Button::init()->text("Create")->icon('check')->color('primary mpost')->formid('duplicateForm')->render();
echo Modal::init()->isInline()->header("Duplicate Quote")->content($pre.$form)->footer($save)->render();