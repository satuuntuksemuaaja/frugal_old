<?php
echo BS::title("Receiving", "Accept Purchase Order Items");
$headers = ['Customer', 'Type', 'PO'];
$rows = [];
foreach (Po::whereArchived(false)->whereStatus('confirmed')->get() AS $po)
{
    $rows[] = [
        ($po->customer) ? $po->customer->name : "Unknown Customer/Internal",
        $po->type,
        Button::init()->text("&nbsp;&nbsp;&nbsp;&nbsp; $po->number &nbsp;&nbsp;&nbsp;&nbsp;")->icon('')->color('primary')->url("/receiving/$po->id")->render()
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$buildup = \vl\facades\bootstrap\Button::init()->text("Go to Buildup")->icon('arrow-right')->url("/buildup")->color('warning btn-lg')->render();
$span = BS::span(12, $buildup. $table);
echo BS::row($span);

echo BS::encap("
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");