<?php
use Carbon\Carbon;
/*
  This function needs value, pre and url to work sent in from the controller.
 */
if (!isset($datevalue)) $datevalue = null;
if (!isset($timevalue) || $timevalue == '12:00 am') $timevalue = "08:00 AM";

$pre = "<center><h4>{$pre}</h4></center><br/><br/><div class='bind'></div>";
if ($datevalue == '11/30/-0001') $datevalue = null;
$fields[] = ['type' => 'input', 'text' => 'Select Date:', 'var' => 'date', 'span' => 3, 'class' => 'dp', 'val' => $datevalue];
$fields[] = ['type' => 'input', 'text' => 'Enter Time: (eg. 9:00 PM)', 'var' => 'time', 'span' => 3, 'class' => 'tp', 'val' => $timevalue];

$form = Forms::init()->id('pickerForm')->elements($fields)->url($url)->render();
$save = Button::init()->text("Save Date/Time")->color('primary mpost')->icon('save')->formid('pickerForm')->render();
if (isset($fail))
{
  $form = $fail;
  $save = null;
}
echo Modal::init()->isInline()->header("Update Date/Time")->content($pre.$form)->footer($save)->render();
echo BS::encap("
$('.dp').datepicker({format: 'mm/dd/yyyy'});
$('.tp').timepicker({
appendWidgetTo: '.modal'});
");