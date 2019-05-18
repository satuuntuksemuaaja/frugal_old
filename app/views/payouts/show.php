<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 11/20/16
 * Time: 7:58 PM
 */
echo BS::title("Payout $payout->id", $payout->user ? $payout->user->name : "Unassigned User");

// Left side is changing the attributes on the payout it self. And approving with total amount.
$opts = [];
if (!$payout->user_id)
{
    $opts[] = ['val' => 0, 'text' => '-- Select User --'];
}
else
    $opts[] = ['val' => $payout->user_id, 'text' => User::find($payout->user_id)->name];
foreach (User::where(function ($t) use ($payout)
{
    $t->where('designation_id', $payout->designation_id);
    $t->orWhere('designation_id', 24);
})->whereActive(true)->get() as $user)
{
    $opts[] = ['val' => $user->id, 'text' => $user->name];
}
$fields[] = ['type' => 'select', 'var' => 'user_id', 'text' => 'User to Pay:', 'opts' => $opts];
$fields[] = ['type' => 'input', 'var' => 'total', 'text' => 'Total To Pay:', 'pre' => "$", 'val' => $payout->total];

$fields[] = ['type' => 'input', 'var' => 'invoice', 'text' => 'Vendor Invoice #:', 'val' => $payout->invoice];
$fields[] = ['type' => 'textarea', 'var' => 'notes', 'text' => 'Notes:', 'val' => $payout->notes, 'span' => 7];
if (Auth::user()->id == 5 || Auth::user()->id == 1 || Auth::user()->id == 7)
{
    $opts = [];
    $opts[] = ['val' => $payout->approved, 'text' => $payout->approved ? "Yes" : "No"];
    $opts[] = ['val' => 0, 'text' => "No"];
    $opts[] = ['val' => 1, 'text' => "Yes"];
    $fields[] = ['type' => 'select', 'var' => 'approved', 'text' => 'Approved? ', 'opts' => $opts];
}

$opts = [];
$opts[] = ['val' => $payout->paid, 'text' => $payout->paid ? "Yes" : "No"];
$opts[] = ['val' => 0, 'text' => "No"];
$opts[] = ['val' => 1, 'text' => "Yes"];
if ($payout->approved)
{
    $fields[] = ['type' => 'select', 'var' => 'paid', 'text' => 'Paid? ', 'opts' => $opts];
}
$fields[] = ['type' => 'input', 'var' => 'check', 'text' => 'Frugal Check #:', 'val' => $payout->check];

$fields[] = ['type' => 'submit', 'var' => 'updatePayout', 'val' => 'Update Payout', 'class' => 'btn-info'];
$form = Forms::init()->id('primaryForm')->labelSpan(4)->elements($fields)->url("/payouts/$payout->id")->render();
$quote = "<a class='btn btn-info' href='/quote/{$payout->job->quote->id}/view'>Sold from Quote {$payout->job->quote->id}</a>";
$quote .= "<a class='btn btn-danger pull-right' href='/payouts/$payout->id/delete'>Delete Payout</a>";
$panel = Panel::init('default')->header("Job List")->content($form)->footer($quote)->render();
$left = BS::span(6, "<a class='btn btn-primary' href='/payouts'>Back to Payouts</a>" . $panel);

// RIght panel is a table with an editor.
$headers = ['Item', 'Amount', 'Delete'];
$rows = [];
foreach ($payout->items AS $item)
{
    $rows[] = [
        "<a href='/payouts/$payout->id?item=$item->id'>$item->item</a>",
        $item->amount,
        "<a href='/payouts/$payout->id?delete=$item->id'><i class='fa fa-trash-o'></i></a>"
    ];
}
$table = Table::init()->headers($headers)->rows($rows);
if (Input::has('item'))
{
    $item = PayoutItem::find(Input::get('item'));
}
else $item = new PayoutItem();
$fields = [];
$fields[] = ['type' => 'input', 'var' => 'item', 'text' => 'Item:', 'val' => strip_tags($item->item), 'span' => 7];
$fields[] = ['type' => 'input', 'var' => 'amount', 'text' => 'Amount:', 'val' => $item->amount];
$fields[] = ['type' => 'submit', 'var' => 'updatePayout', 'val' => 'Update Item', 'class' => 'btn-info'];
$form = Forms::init()->id('primaryForm')->labelSpan(4)->elements($fields)
    ->url("/payouts/$payout->id?items=true&itemid=$item->id")->render();
$panel = Panel::init('default')->header("Items for Payout ( <a href='/payouts/$payout->id'>Clear Form</a> ) ")
    ->content($form)->render();


$right = BS::span(6, $table . $panel);


echo BS::row($left . $right);
