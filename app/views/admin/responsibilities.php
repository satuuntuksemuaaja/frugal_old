<?php
$headers = ['Responsibility'];
$rows = [];
$responsibilities = Responsibility::whereActive(true)->get();
foreach ($responsibilities AS $responsibility)
{
    $rows[] = [
        "<a href='/admin/responsibilities/$responsibility->id'>$responsibility->name</a>",
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);
$fields = [];
$responsibility = (isset($id)) ? Responsibility::find($id) : new Responsibility;
$fields[] = ['type' => 'textarea', 'text' => 'Responsibility:', 'var' => 'name', 'val' => $responsibility->name, 'span' => 7];

$title = ($responsibility->id) ? "Edit $responsibility->name" : "New responsibility";
$url = ($responsibility->id) ? "/admin/responsibilities/$responsibility->id" : "/admin/responsibilities";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $responsibility->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/responsibilities/$responsibility->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);