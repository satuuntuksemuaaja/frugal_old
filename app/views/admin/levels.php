<?php
$headers = ['Level', 'Users'];
$rows = [];
$levels = Level::whereActive(true)->get();
foreach ($levels AS $level)
  $rows[] = ["<a href='/admin/levels/$level->id'>$level->name</a>",
      $level->users()->count()];
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);



$level = (isset($id)) ? Level::find($id) : new Level;
$fields = [];
$fields[] = ['type' => 'input', 'var' => 'name', 'val' => $level->name, 'span' => 7, 'text' => 'Access Level:'];

$title = ($level->id) ? "Edit $level->name" : "New Access Level";
$url = ($level->id) ? "/admin/levels/$level->id" : "/admin/levels";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $level->id ?
  Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/levels/$level->id/delete")->render() :
  null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save.$delete)->render();
$span .= BS::span(4, $panel);
echo BS::row($span);