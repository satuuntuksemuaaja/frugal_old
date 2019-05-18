<?php
use Carbon\Carbon;

echo BS::title("Purchase Orders", "Manage Purchase Orders");
$headers = [
    '#',
    'Title',
    'Customer',
    'Vendor',
    'Type',
    'Created By',
    'Status',
    'Ordered On',
    'Company Invoice',
    'Projected Ship Date'
];
$rows = [];
$old = Button::init()->text("Show old Purchase Orders")->color('info')->icon('refresh')->url("?old=true")->render();
$export = Button::init()->text("Export POs")->color('default')->icon('download')->url("?export=true")->render();

$add = Button::init()->text("Create Purchase Order")->color('primary')->icon('plus')->modal('newOrder')
        ->render() . $old . $export . "<br/><br/>";

if (Input::has('old'))
{
    $pos = Po::all();
}
else
{
    $pos = Po::whereParentId(0)->orderBy('submitted', 'ASC')->get();
}
foreach ($pos as $po)
{
    if (Input::has('old'))
    {
        $rows[] = render($po, $po->parent_id);
        foreach ($po->children as $child)
        {
            if (!$child->archived)
                $rows[] = render($child, $child->parent_id);
        }
    }
    else
    {
        if (!$po->archived)
        {
            $rows[] = render($po, $po->parent_id);
        }
        if ($po->children()->count() > 0)
        {
            foreach ($po->children as $child)
            {
                if (!$child->archived)
                {
                    $rows[] = render($child, $child->parent_id);
                }
            }
        }
    }
}


$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(12, $add . $table);
$row = BS::row($span);
echo $row;

$pre = "<h4>You are creating a new purchase order. The purchase order will remain in the draft stage until it is
considered submitted to the vendor for purchase.</h4>";
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'PO Description:', 'var' => 'title', 'span' => '7'];
$customers = Customer::all();
$opts[] = ['val' => 0, 'text' => '-- Internal Purchase Order --'];
foreach ($customers AS $customer)
{
    $opts[] = ['val' => $customer->id, 'text' => "($customer->id) $customer->name"];
}
$fields[] = [
    'type'  => 'select2',
    'var'   => 'customer_id',
    'opts'  => $opts,
    'span'  => 7,
    'text'  => 'Select Customer:',
    'width' => 400
];
$opts = [];
$vendors = Vendor::all();
$opts[] = ['val' => 0, 'text' => '-- Select Vendor --'];
foreach ($vendors AS $vendor)
{
    $opts[] = ['val' => $vendor->id, 'text' => $vendor->name];
}
$fields[] = [
    'type'  => 'select2',
    'var'   => 'vendor_id',
    'opts'  => $opts,
    'span'  => 7,
    'text'  => 'Select Vendor:',
    'width' => 400
];
$form = Forms::init()->id('newOrderForm')->labelSpan(4)->elements($fields)->url("/pos/new")->render();
$save = Button::init()->text("Save")->icon('save')->color('primary mpost')->formid('newOrderForm')->render();
echo Modal::init()->id('newOrder')->header("New Purchase Order")->content($pre . $form)->footer($save)->render();
echo Modal::init()->id('workModal')->onlyConstruct()->render();


function render($po, $child = false)
{
    $color = $child ? 'color-primary' : null;
    $ordered = Carbon::parse($po->submitted);
    if ($ordered->timestamp > 0)
    {
        $ordered = $ordered->format("m/d/y");
    }
    else $ordered = "<span class='text-danger'>Not Ordered</span>";

    $status = $po->status;
    if ($po->status == 'draft')
    {
        $status .= "<span class='pull-right'> (<a class='get' href='/po/$po->id/order'>order</a>)</span>";
    }
    else
    {
        if ($po->status == 'ordered')
        {
            $status .= "<span class='pull-right'> (<a class='get' href='/po/$po->id/confirm'>confirm</a>)</span>";
        }
    }
    if ($status == 'complete')
    {
        $status = "<span style='color: #25fe29;'>COMPLETE</span>";
    }
    if ($po->job && $po->job->quote)
    {
        $files = "<span class='pull-right'><a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                data-original-title='Drawings' data-toggle='modal' data-target='#workModal'
                href='/quote/{$po->job->quote->id}/files'><i class='fa fa-image'></i></a></span>";
    }
    else $files = null;
    $delete = "<span class='pull-right'>";
    $delete .= ($po->items()->count() == 0) ? "<span class='pull-right'>
              <a class='tooltiped get' href='/po/$po->id/delete' data-toggle='tooltip' data-placement='right'
                data-original-title='Delete PO'><i class='fa fa-times'></i></a> &nbsp; &nbsp; " : null;
    $delete .= (Auth::user()->id == 5 || Auth::user()->id == 1 || Auth::user()->id == 7) && !$po->archived
        ? " <a href='/pos/$po->id/archive'><i class='fa fa-eraser'></i></a>"
        : null;
    $delete .= "</span>";

    if (!$po->company_invoice) $po->company_invoice = 'empty';
    if (!$po->projected_ship) $po->projected_ship = 'empty';
    return [
        "$color <a href='/po/$po->id'>$po->number</a>{$files}",
        "<a href='/po/$po->id'>$po->title</a> {$delete}",
        ($po->customer) ? "<a href='/profile/{$po->customer->id}/view'>{$po->customer->name}</a>" : "Internal Purchase Order",
        ($po->vendor) ? $po->vendor->name : "Unknown Vendor",
        Editable::init()->id("idLo_$po->id")->placement('left')->pk(1)->type('text')->title("Type of Purchase Order")
            ->linkText($po->type)->url("/po/$po->id/type")->render(),
        ($po->user) ? $po->user->name : "System",
        $status,
        $ordered,
        Editable::init()->id("idLo_$po->id")->placement('left')->pk(1)->type('text')->title("Company Purchase Order")
            ->linkText($po->company_invoice)->url("/po/$po->id/inv")->render(),
        Editable::init()->id("idLo_$po->id")->placement('left')->pk(1)->type('text')->title("Projected Ship Date")
            ->linkText($po->projected_ship)->url("/po/$po->id/projected")->render(),

    ];
}
