<?php
$headers = ['SKU', 'Name', 'Vendor', 'Description'];
$rows = [];
$accessories = Accessory::orderBy('sku', 'ASC')->whereActive(true)->get();
foreach ($accessories AS $accessory)
{
    if (!$accessory->sku) $accessory->sku = "Unknown";
    $rows[] = [
        "<a href='/admin/accessories/$accessory->id'>{$accessory->sku}</a>",
        $accessory->name,
        ($accessory->vendor) ? $accessory->vendor->name : "No Vendor Found",
        $accessory->description
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(8, $table);


$fields = [];
$accessory = (isset($id)) ? Accessory::find($id) : new Accessory;
$fields[] = ['type' => 'input', 'text' => 'SKU:', 'var' => 'sku', 'val' => $accessory->sku, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Name:', 'var' => 'name', 'val' => $accessory->name, 'span' => 7];
$fields[] = [
    'type' => 'textarea',
    'text' => 'Description:',
    'var'  => 'description',
    'val'  => $accessory->description,
    'span' => 7
];
$fields[] = ['type' => 'input', 'text' => 'Price:', 'var' => 'price', 'val' => $accessory->price, 'span' => 7];
$fields[] = ['type' => 'file', 'text' => 'Image:', 'var' => 'image', 'span' => 7];

$opts = [];
foreach (Vendor::all() AS $vendor)
{
    $opts[] = ['val' => $vendor->id, 'text' => $vendor->name];
}
if ($accessory->vendor_id)
{
    array_unshift($opts, ['val' => $accessory->vendor_id, 'text' => $accessory->vendor->name]);
}
else
{
    array_unshift($opts, ['val' => 0, 'text' => '-- Select Vendor -- ']);
}
$fields[] = ['type' => 'select2', 'text' => 'Vendor:', 'opts' => $opts, 'span' => 7, 'var' => 'vendor_id'];
$opts = [];
$opts[] = ['val' => 'Y', 'text' => 'Accessory installed on site?', 'checked' => $accessory->on_site ? true : false];
$fields[] = ['type' => 'checkbox', 'var' => 'on_site', 'opts' => $opts, 'span' => 7];
$fields[] = ['type' => 'submit', 'text' => 'Save', 'var' => 'save', 'val' => 'Save', 'class' => 'btn-primary'];

$title = ($accessory->id) ? "Edit $accessory->sku" : "New accessory";
$url = ($accessory->id) ? "/admin/accessories/$accessory->id" : "/admin/accessories";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$image = $accessory->image ? "<img class='img-responsive' src='/acc_images/$accessory->image'>" : null;

$delete = $accessory->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')
        ->url("/admin/accessories/$accessory->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form . $image)->footer($delete)->render();
$span .= BS::span(4, $panel);

echo BS::row($span);