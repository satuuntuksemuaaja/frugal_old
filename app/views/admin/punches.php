<?php
$headers = ['Question', 'Designation'];
$rows = [];
$punches = Punch::whereActive(true)->get();
foreach ($punches AS $punch)
{
    $rows[] = [
        "<a href='/admin/punches/$punch->id'>$punch->question</a>",
        $punch->designation->name
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);
$fields = [];
$punch = (isset($id)) ? Punch::find($id) : new Punch;
$fields[] = ['type' => 'textarea', 'text' => 'Question:', 'var' => 'question', 'val' => $punch->question, 'span' => 7];
$opts = [];
foreach (Designation::all() as $designation)
{
    $opts[] = ['val' => $designation->id, 'text' => $designation->name];
}
$opts[] = ['val' => 0, 'text' => 'None (Frugal)'];
if ($punch->designation_id)
{
    array_unshift($opts, ['val' => $punch->designation_id, 'text' => $punch->designation->name]);
}
else
{
    array_unshift($opts, ['val' => 0, 'text' => 'None']);
}
$fields[] = ['type' => 'select', 'text' => 'Designation', 'opts' => $opts, 'span' => 7, 'var' => 'designation_id'];

$title = ($punch->id) ? "Edit $punch->name" : "New punch";
$url = ($punch->id) ? "/admin/punches/$punch->id" : "/admin/punches";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $punch->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/punches/$punch->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);