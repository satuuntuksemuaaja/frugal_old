<?php
$headers = ['Frugal Name', 'Cabinet Name', 'Vendor'];
$rows = [];
$cabinets = Cabinet::orderBy('frugal_name', 'ASC')->whereActive(true)->get();
foreach ($cabinets AS $cabinet)
  $rows[] = ["<a href='/admin/cabinets/$cabinet->id'>{$cabinet->frugal_name}</a>",
            $cabinet->name,
            ($cabinet->vendor) ? $cabinet->vendor->name : "Unknown Vendor"
            ];

$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(8, $table);

$fields = [];
$cabinet = (isset($id)) ? Cabinet::find($id) : new Cabinet;
$fields[] = ['type' => 'input', 'text' => 'Cabinet Name:', 'var' => 'name', 'val' => $cabinet->name, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Cabinet Frugal Name:', 'var' => 'frugal_name', 'val' => $cabinet->frugal_name, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Description:', 'var' => 'description', 'val' => $cabinet->description, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Price:', 'var' => 'price', 'val' => $cabinet->price, 'span' => 7];
$fields[] = ['type' => 'file', 'text' => 'Cabinet Image:', 'var' => 'image', 'span' => 7];

$opts = [];
foreach (Vendor::all() AS $vendor)
  $opts[] = ['val' => $vendor->id, 'text' => $vendor->name];
if ($cabinet->vendor_id)
  array_unshift($opts, ['val' => $cabinet->vendor_id, 'text' => $cabinet->vendor->name]);
else
  array_unshift($opts, ['val' => 0, 'text' => '-- Select Vendor -- ']);
$fields[] = ['type' => 'select2', 'text' => 'Vendor:', 'opts' => $opts, 'span' => 7, 'var' => 'vendor_id'];
$title = ($cabinet->id) ? "Edit $cabinet->name" : "New cabinet";
$url = ($cabinet->id) ? "/admin/cabinets/$cabinet->id" : "/admin/cabinets";
$fields[] = ['type' => 'submit', 'text' => 'Save', 'var' => 'save', 'val' => 'Save', 'class' => 'btn-primary'];

$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
//$save = Button::init()->text("Save")->icon('check')->color('primary')->formid('editForm')->render();
$delete = $cabinet->id ?
  Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/cabinets/$cabinet->id/delete")->render() :
  null;
$image = $cabinet->image ? "<img class='img-responsive' src='/cabinet_images/$cabinet->image'>" : null;
$panel = Panel::init('primary')->header($title)->content($form.$image)->footer($delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);