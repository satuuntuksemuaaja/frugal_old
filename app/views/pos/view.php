<?php

use Carbon\Carbon;

$customer = ($po->customer) ? $po->customer->name : "Internal Purchase Order (Frugal Kitchens)";
echo BS::title("#$po->number", $customer . " for Job $po->job_id");
$add = Button::init()->text("Add Item")->color('primary')->icon('plus')->modal('newItem')->render();
if ($po->parent_id == 0)
{
    $add .= Button::init()->text("Spawn Sub-PO")->color('info')->icon('plus')->url("/po/$po->id/child")
            ->render() . "<br/><br/>";
}

$headers = ['Quantity', 'Item', 'Status', 'Remove'];
$rows = [];
foreach ($po->items AS $item)
{
    if ($item->received_by)
    {
        $unverify = "<a class='get' href='/item/$item->id/unverify'> (Unverify Item)</a>";
        $status = "Received on " . Carbon::parse($item->received)
                ->format("m/d/y h:i a") . " by " . $item->receivedby->name . " " . $unverify;
    }
    elseif ($po->status == 'confirmed')
    {
        $status = Button::init()->text("Receive Item")->icon('arrow-right')->color('success get')
            ->url("/po/$po->id/item/$item->id/receive")->render();
    }
    elseif ($po->archived)
    {
        $status = "<i>This PO has been archived/closed</i>";
    }
    else
    {
        $status = "<b>PO Not Confirmed</b>";
    }
    if ($item->punch) $item->item .= "<br/><span class='text-danger'>** This is a punch list item **</span>";
    $itemName = $item->item;
    if ($item->fft)
        $itemName .= "<br/><small class='text-success'>(FFT) {$item->fft->reference}</small>";
    if ($item->service)
        $itemName .= "<br/><small class='text-info'>(Service) {$item->service->reference}</small>";
    if ($item->warranty)
        $itemName .= "<br/><small class='text-danger'>(Warranty) {$item->warranty->reference}</small>";

    $rows[] = [
        $item->qty,
        $itemName,
        $status,
        "<a class='get' href='/po/$po->id/item/$item->id/remove'><i class='fa fa-trash-o'></i></a>"
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(10, $add . $table, 1);
$row = BS::row($span);
echo $row;

// Modal for Adding item
$pre = "<h4>You are adding an item to PO: #$po->number. Please ensure this is from the same vendor so as not to cause confusion.</h4>";
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Quantity:', 'var' => 'qty', 'span' => 4];
$fields[] = ['type' => 'textarea', 'var' => 'item', 'text' => 'Item:', 'span' => 6];
$opts = [];
// Punch Fields
$opts[] = ['val' => 0, 'text' => 'No Punch Item'];
foreach (JobItem::whereJobId($po->job_id)->whereInstanceof('FFT')->wherePoItemId(0)->where('reference', '!=', '')
             ->get() as $item)
{
    $opts[] = ['val' => $item->id, 'text' => $item->reference];
}
$fields[] = ['type' => 'select', 'var' => 'punch_item_id', 'text' => 'From Punch:', 'span' => 6, 'opts' => $opts];
$ffts = [];
// Check FFT for FFT/Warranty/Service Item
if ($po->job_id)
{
    $ffts = FFT::whereJobId($po->job_id)->get();
}
else
{
    // Get Jobs for a customer.
    foreach ($po->customer->quotes AS $quote)
    {
        if ($quote->job)
        {
            $ffts = FFT::whereJobId($quote->job->id)->get();
        }
    }
}

$warrantyOpts = [];
$fftOpts = [];
$serviceOpts = [];
foreach ($ffts as $fft)
{
    if (!$fft->job) continue;
    $title = $fft->job->quote->title ?: "Original Job";
    if ($fft->warranty)
    {
        foreach ($fft->job->items()->whereInstanceof('Warranty')->get() as $item)
        $warrantyOpts[] = ['val' => $item->id, 'text' => "($item->id) $item->reference"];
    }
    elseif ($fft->service)
    {
        foreach ($fft->job->items()->whereInstanceof('Service')->get() as $item)
            $serviceOpts[] = ['val' => $item->id, 'text' => "($item->id) $item->reference"];
    }
    else
    {
        foreach ($fft->job->items()->whereInstanceof('FFT')->get() as $item)
            $fftOpts[] = ['val' => $item->id, 'text' => "($item->id) $item->reference"];
    }
}
if (!empty($fftOpts))
{
    $fftOpts = array_merge([0 => '-- Select FFT --'], $fftOpts);
    $fields[] = ['type' => 'select', 'var' => 'fft_id', 'text' => 'From FFT:', 'span' => 6, 'opts' => $fftOpts];
}
if (!empty($serviceOpts))
{
    $serviceOpts = array_merge([0 => '-- Select Service Work --'], $serviceOpts);
    $fields[] = [
        'type' => 'select',
        'var'  => 'service_id',
        'text' => 'From Service Work:',
        'span' => 6,
        'opts' => $serviceOpts
    ];
}
if (!empty($warrantyOpts))
{
    $warrantyOpts = array_merge([0 => '-- Select Warranty --'], $warrantyOpts);

    $fields[] = [
        'type' => 'select',
        'var'  => 'warranty_id',
        'text' => 'From Warranty:',
        'span' => 6,
        'opts' => $warrantyOpts
    ];
}


$form = Forms::init()->id('newOrderForm')->labelSpan(4)->elements($fields)->url("/po/$po->id/items/new")->render();
$save = Button::init()->text("Save")->icon('save')->color('primary mpost')->formid('newOrderForm')->render();
echo Modal::init()->id('newItem')->header("New Purchase Order Item")->content($pre . $form)->footer($save)->render();

