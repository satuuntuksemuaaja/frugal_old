<?php
$pre = BS::callout('danger', "You are about to override XML files for ordering cabinets. This will not change
  the contract but will change which items you are ordering and verifying. Proceed with caution!");
$fields = [];
foreach ($job->quote->cabinets AS $cabinet)
{
   $fields[] = ['type' => 'file', 'var' => "cabinet_{$cabinet->id}", 'text' => "{$cabinet->cabinet->frugal_name} Override", 'comment' => $cabinet->description];
   $fields[] = ['type' => 'hidden', 'var' => "c_$cabinet->id", 'val' => 'Y'];
}


$fields[] = ['type' => 'submit', 'var' => 'go', 'val' => "Upload", 'class' => 'primary'];
$form = Forms::init()->id('newXMLForm')->labelSpan(4)->elements($fields)->url("/job/$job->id/xml")->render();
echo Modal::init()->isInline()->header("Override XML")->content($pre.$form)->render();