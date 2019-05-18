<?php
use Carbon\Carbon;
$customer = ($po->customer) ? $po->customer->name : "Internal Purchase Order (Frugal Kitchens)";
echo BS::title("#$po->number", $customer);
echo Button::init()->text("Go back")->icon('arrow-left')->color('primary btn-lg')->url("/receiving")->render();


$headers = ['Quantity', 'Item', 'Status'];
$rows = [];
foreach ($po->items AS $item)
{
  if ($item->received_by)
  {
    $unverify = "<a class='get' href='/receiving/item/$item->id/unverify'> (Unverify Item)</a>";
    $status = "Received on " . Carbon::parse($item->received)->format("m/d/y h:i a") . " by " . $item->receivedby->name . " " . $unverify;
  }
  elseif ($po->status == 'confirmed')
    $status = Button::init()->text("Receive Item")->icon('arrow-right')->color('success get')->url("/receiving/$po->id/item/$item->id/receive")->render();
  elseif ($po->archived)
    $status = "<i>This PO has been archived/closed</i>";
  else
    $status = "<b>PO Not Confirmed</b>";

  $rows[] = [
    $item->qty,
    $item->item,
    $status
  ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(10, $table, 1);
$row = BS::row($span);
echo $row;


echo BS::encap("
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");