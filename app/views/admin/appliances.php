<?php
$headers = ['Name', 'Additional Cost', 'Counts As', 'Goes To'];
$rows = [];
$appliances = Appliance::orderBy('name', 'ASC')->whereActive(true)->get();
foreach ($appliances AS $appliance)
  $rows[] = ["<a href='/admin/appliances/$appliance->id'>$appliance->name</a>",
            $appliance->price,
            $appliance->countas,
            ($appliance->designation_id) ? $appliance->designation->name : "None"
            ];

$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(8, $table);

$fields = [];
$appliance = (isset($id)) ? Appliance::find($id) : new Appliance;
$fields[] = ['type' => 'input', 'text' => 'Appliance Name:', 'var' => 'name', 'val' => $appliance->name, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Price:', 'var' => 'price', 'val' => $appliance->price, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Counts As:', 'var' => 'countas', 'val' => $appliance->countas, 'span' => 3];
$opts = [];
foreach (Designation::all() AS $designation)
  $opts[] = ['val' => $designation->id, 'text' => $designation->name];
if ($appliance->designation_id)
  {
    array_unshift($opts, ['val' => $appliance->designation_id, 'text' => $appliance->designation->name]);
    $opts[] = ['val' => 0, 'text' => 'None'];
  }
  else
    array_unshift($opts, ['val' => 0, 'text' => 'None']);
$fields[] = ['type' => 'select', 'var' => 'designation_id', 'opts' => $opts, 'text' => 'Goes To:', 'span' => 7];
$title = ($appliance->id) ? "Edit $appliance->name" : "New appliance";
$url = ($appliance->id) ? "/admin/appliances/$appliance->id" : "/admin/appliances";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $appliance->id ?
  Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/appliances/$appliance->id/delete")->render() :
  null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save.$delete)->render();
$span .= BS::span(4, $panel);



echo BS::row($span);