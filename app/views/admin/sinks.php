<?php
$headers = ['Name', 'Price', 'Material'];
$rows = [];
$sinks = Sink::orderBy('name', 'ASC')->whereActive(true)->get();
foreach ($sinks AS $sink)
  $rows[] = ["<a href='/admin/sinks/$sink->id'>$sink->name</a>",
            $sink->price,
            $sink->material,
            ];

$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);

$fields = [];
$sink = (isset($id)) ? Sink::find($id) : new Sink;
$fields[] = ['type' => 'input', 'text' => 'Sink Name:', 'var' => 'name', 'val' => $sink->name, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Price:', 'var' => 'price', 'val' => $sink->price, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Material:', 'var' => 'material', 'val' => $sink->material, 'span' => 7];


$title = ($sink->id) ? "Edit $sink->name" : "New Sink";
$url = ($sink->id) ? "/admin/sinks/$sink->id" : "/admin/sinks";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $sink->id ?
  Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/sinks/$sink->id/delete")->render() :
  null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save.$delete)->render();
$span .= BS::span(4, $panel);



echo BS::row($span);