<?php
echo BS::title("Addons", $quote->lead->customer->name);
$headers = ['Item', 'QTY', 'Price', 'Ext. Price'];
$rows = [];
foreach ($quote->addons AS $addon)
{
    $add = $addon->description ? "<br/><small>$addon->description</small>" : null;
    if (!$addon->addon)
    {
        $addon->delete();
        continue;
    }
    $rows[] = [
        "<a href='/quote/{$quote->id}/addons?edit=$addon->id'>{$addon->addon->item}</a> 
            <span class='pull-right'><a class='get' href='/quote/{$quote->id}/addons?delete=$addon->id'><i class='fa fa-times'></i></a></span> $add",
        $addon->qty,
        "$" . number_format($addon->price, 2),
        "$" . number_format($addon->qty * $addon->price, 2)
    ];
}
$table = Table::init()->headers($headers)->rows($rows);

// Determine if we are editing an addon or creating a new.
if (Request::has('edit'))
{
    $addon = QuoteAddon::find(Request::get('edit'));
}
else $addon = new QuoteAddon();

$opts = [];
if (!$addon->addon_id)
{
    $opts[] = ['val' => 0, 'text' => '-- Select Addon --'];
}
else
{
    $opts[] = ['val' => $addon->id, 'text' => $addon->addon->item];
}
foreach (Addon::whereActive(true)->get() as $add)
{
    $opts[] = ['val' => $add->id, 'text' => $add->item];
}
$fields[] = [
    'type' => 'select2',
    'text' => 'Item:',
    'var'  => 'item_id',
    'opts' => $opts,
    'width' => 400

];
$fields[] = [
    'type'  => 'input',
    'span'  => 4,
    'var'   => 'qty',
    'text'  => 'Quantity:',
    'val'   => $addon->qty ?: 1
];

$fields[] = [
    'type' => 'input',
    'span' => 8,
    'var'  => 'price',
    'text' => 'Price:',
    'pre'  => "$",
    'post' => 'Leave price at 0 to use default amount.',
    'val'  => $addon->price ?: 0
];


$fields[] = [
    'type' => 'textarea',
    'span' => 8,
    'var'  => 'description',
    'text' => 'Description:',
    'val'  => $addon->description,
    'comment' => 'This will be shown in the contract'
];

if ($addon->id)
{
    $tag = "?update=$addon->id";
}
else $tag = null;


$form = Forms::init()->id('primaryForm')->labelSpan(4)->elements($fields)->url("/quote/$quote->id/addons{$tag}")
    ->render();
$save = "<center>" . Button::init()->text("Save Addon")->color('danger post pulse-red')->formid('primaryForm')
        ->icon('save')->render();
if ($addon->id)
{
    $save .= "&nbsp; &nbsp; " . Button::init()->text("Create New Addon")->color('success')
            ->url("/quote/$quote->id/addons")->icon('plus')->render();
}
$save .= Button::init()->text("Quote Overview")->color('info')->icon('share')
    ->url("/quote/$quote->id/view")->render();

$save .= "</center>";
$panel = Panel::init('primary')->header("Quote Addons")->content($form)->footer($save)->render();

$left = BS::span(6, $table . $panel);

// -- Customer responsibiilities
$headers = ['', 'Responsibility'];
$rows = [];$rows = [];
$form = "<form id='responsibilityForm' method='post' action='/quote/$quote->id/responsibilities'>";
$rs = Responsibility::whereActive(true)->orderBy('name', 'ASC')->get();
foreach ($rs AS $r)
{
    $checked = $quote->responsibilities()->whereResponsibilityId($r->id)->count();
    if ($checked > 0) $c = 'checked';
    else $c = '';
    $rows[] = ["<input type='checkbox' name='rs_$r->id' value='1' $c>",
               $r->name,
    ];
}
$table = $form . Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$save = Button::init()->text("Save Responsibilities")->color('danger post pulse-red')->formid('responsibilityForm')->icon('save')->render();
$rPanel = Panel::init('info')->header("Select Customer Responsibilities")->content($table)->footer("<center>$save</center></form>")->render();

$right = BS::span(6, $rPanel);
echo BS::row($left.$right);


