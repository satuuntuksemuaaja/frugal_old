<?php
$headers = ['Source'];
$rows = [];
$sources = LeadSource::whereActive(true)->get();
foreach ($sources AS $source)
  $rows[] = ["<a href='/admin/sources/$source->id'>$source->type</a>"];

$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);

$source = (isset($id)) ? LeadSource::find($id) : new LeadSource;
$fields = [];
$fields[] = ['type' => 'input', 'var' => 'type', 'text' => 'Source Name:', 'val' => $source->type, 'span' => 7];

$title = ($source->id) ? "Edit $source->type" : "New Lead Source";
$url = ($source->id) ? "/admin/sources/$source->id" : "/admin/sources";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $source->id ?
  Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/sources/$source->id/delete")->render() :
  null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save.$delete)->render();
$span .= BS::span(4, $panel);
echo BS::row($span);
