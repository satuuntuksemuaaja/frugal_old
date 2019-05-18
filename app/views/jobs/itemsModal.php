<?php
use vl\jobs\JobBoard;
use Carbon\Carbon;

// New items modal and verify.
// Check items in the quote, if one is not found, then add it.
JobBoard::checkJobItems($job); // First make sure all our items are there and nothing was updated.
$items = JobItem::whereJobId($job->id)->whereInstanceof('Item')->get();
$headers = ['Item', 'Verified On'];
$rows = [];
foreach ($items AS $item)
{

    $verifyButton = Button::init()->text("Verify")->color('info btn-xs get')->icon('check')
        ->url("/item/$item->id/verify")->render();
    $deleteButton = Button::init()->text("Delete")->color('danger btn-xs get')->icon('trash-o')
        ->url("/item/$item->id/delete")->render();

    if ($item->verified == '0000-00-00')
    {
        $rows[] = [$item->reference, $verifyButton . $deleteButton];
    }
    else
    {
        $rows[] = [$item->reference, Carbon::parse($item->verified)->format('m/d/y h:i a')];
    }

}
$table = Table::init()->headers($headers)->rows($rows)->render();

$fields = [];
$fields[] = ['type' => 'input', 'var' => 'item', 'text' => 'New Item:', 'span' => 7];
$save = Button::init()->text("Add Item")->color('primary post')->icon('plus')->formid('editCabinetForm')
    ->icon('save')->withoutGroup()->render();
$form = Forms::init()->id('editCabinetForm')->labelSpan(4)->url("/fft/$job->id/items")
    ->elements($fields)->render();


echo Modal::init()->isInline()->header("Verify Items")->content($table . "<hr/>" . $form . $save)->render();
