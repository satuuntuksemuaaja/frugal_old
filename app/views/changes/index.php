<?php
use Carbon\Carbon;

echo BS::title("Change Orders", "Modify Job Parameters");
$headers = ['#', 'Job', 'Created', 'By', 'Sent', 'Signed', 'Parts Ordered', 'Items'];
$rows = [];
if (Input::has('all'))
{
    $orders = ChangeOrder::all();
}
else
{
    $orders = ChangeOrder::whereClosed(false)->get();
}
foreach ($orders as $order)
{
    if (!$order->job) continue;
    if (!$order->job->quote) continue;
    if (!$order->job->quote->lead) continue;
    if ($order->items()->whereOrderable(true)->count() > 0)
    {
        if ($order->ordered)
            $ordered = "Yes";
        else
            $ordered = "No";
    }
    else
        $ordered = "N/A";
    $closed = $order->closed ? "<span class='pull-right' style='color:red;'>(closed)</span>" : null;
    $closeAction = "<i class='fa fa-times'></i>";
    $rows[] = [
        "<a href='/change/$order->id'><b>#$order->id</b></a> <span class='pull-right'><a class='get' href='/change/$order->id/close'>$closeAction</a></span>",
        "<a href='/profile/{$order->job->quote->lead->customer->id}/view'>{$order->job->quote->lead->customer->name}</a> $closed",
        $order->created_at->format("m/d/y"),
        $order->user->name,
        ($order->sent) ? Carbon::parse($order->sent_on)->format("m/d/y") : "No",
        ($order->signed) ? Carbon::parse($order->signed_on)->format("m/d/y") . " (<a class='get' href='/change/$order->id/send'>re-send to customer)</a>" : "No (<a class='get' href='/change/$order->id/send'>send to customer)</a>",
        $ordered,
        $order->items->count(),
    ];
}
$add = Button::init()->text("Add Change Order")->icon('plus')->color('primary')->modal('newChange')->render();
$all = Button::init()->text("Show All Orders")->icon('refresh')->color('warning')->url("?all=true")->render();
$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(12, $add . $all . $table);
echo BS::row($span);

// Change Modal
$pre = "<h4>You are adding a new change order. This should only be done once a job has been started and a new item is added mid-job.
Customers will be emailed a copy of the change order once complete to sign.</h4>
<h5><b>NOTE:</b> Once a customer signs a change order it is locked and no other items can be added. If the customer requests additional items
after the change order has been signed, a new (additional) change order will need to be created.</h5>";
$fields = [];
$jobs = Job::all();
$opts[] = ['val' => 0, 'text' => '-- Select Job --'];
foreach ($jobs AS $job)
    @$opts[] = ['val' => $job->id, 'text' => $job->quote->lead->customer->name . " ({$job->quote->type} - $job->id)"];
$fields[] = ['type' => 'select2', 'var' => 'job_id', 'opts' => $opts, 'span' => 6, 'text' => 'Job:', 'width' => 400];
$form = Forms::init()->id('newOrderForm')->labelSpan(4)->elements($fields)->url("/changes/new")->render();
$save = Button::init()->text("Save")->icon('save')->color('primary mpost')->formid('newOrderForm')->render();
echo Modal::init()->id('newChange')->header("New Change Order")->content($pre . $form)->footer($save)->render();
