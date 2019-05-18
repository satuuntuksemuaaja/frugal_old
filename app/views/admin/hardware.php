<?php
$headers = ['SKU', 'Description', 'Price', 'Vendor'];
$rows = [];
$hardwares = Hardware::orderBy('sku', 'ASC')->whereActive(true)->get();
foreach ($hardwares AS $hardware)
  $rows[] = ["<a href='/admin/hardware/$hardware->id'>{$hardware->sku}</a>",
            $hardware->description,
            $hardware->price,
            ($hardware->vendor) ? $hardware->vendor->name : "No Vendor Found"
            ];

$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(8, $table);

$fields = [];
$hardware = (isset($id)) ? Hardware::find($id) : new Hardware;
$fields[] = ['type' => 'input', 'text' => 'SKU:', 'var' => 'sku', 'val' => $hardware->sku, 'span' => 7];
$fields[] = ['type' => 'textarea', 'text' => 'Description:', 'var' => 'description', 'val' => $hardware->description, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Price:', 'var' => 'price', 'val' => $hardware->price, 'span' => 7];
$fields[] = ['type' => 'file', 'text' => 'Image:', 'var' => 'image', 'span' => 7];

$opts = [];
foreach (Vendor::all() AS $vendor)
  $opts[] = ['val' => $vendor->id, 'text' => $vendor->name];
if ($hardware->vendor_id)
  array_unshift($opts, ['val' => $hardware->vendor_id, 'text' => $hardware->vendor->name]);
else
  array_unshift($opts, ['val' => 0, 'text' => '-- Select Vendor -- ']);
$fields[] = ['type' => 'select2', 'text' => 'Vendor:', 'opts' => $opts, 'span' => 7, 'var' => 'vendor_id'];
$fields[] = ['type' => 'submit', 'text' => 'Save', 'var' => 'save', 'val' => 'Save', 'class' => 'btn-primary'];

$title = ($hardware->id) ? "Edit $hardware->sku" : "New hardware";
$url = ($hardware->id) ? "/admin/hardware/$hardware->id" : "/admin/hardware";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$delete = $hardware->id ?
  Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/hardware/$hardware->id/delete")->render() :
  null;
$image = $hardware->image ? "<img class='img-responsive' src='/hardware_images/$hardware->image'>" : null;

$panel = Panel::init('primary')->header($title)->content($form.$image)->footer($delete)->render();
$span .= BS::span(4, $panel);




echo BS::row($span);