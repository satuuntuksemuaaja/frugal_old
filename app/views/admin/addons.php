<?php
$headers = ['Item', 'Designation', 'Price'];
$rows = [];
$addons = Addon::whereActive(true)->get();
foreach ($addons AS $addon)
{
    $rows[] = [
        "<a href='/admin/addons/$addon->id'>$addon->item</a>",
        $addon->designation ? $addon->designation->name : "No Designation Assigned",
        $addon->price
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->datatables()->render();
$span = BS::span(8, $table);
$fields = [];
$addon = (isset($id)) ? Addon::find($id) : new Addon;
$fields[] = ['type' => 'input', 'text' => 'Item:', 'var' => 'item', 'val' => $addon->item, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Price:', 'var' => 'price', 'val' => $addon->price, 'span' => 7, 'pre' => "$"];
$fields[] = ['type' => 'textarea', 'text' => 'Contract Verbiage:', 'var' => 'contract', 'val' => $addon->contract, 'span' => 7];
$opts = [];
if ($addon->designation_id)
    $opts[] = ['val' => $addon->designation_id, 'text' => $addon->designation->name];
$opts[] = ['val' => 0, 'text' => '-- No Designation --'];
foreach (Designation::whereActive(true)->get() as $des)
{
    $opts[] = ['val' => $des->id, 'text' => $des->name];
}
$fields[] = ['type' => 'select2', 'text' => 'Designation:', 'var' => 'designation_id', 'opts' => $opts, 'span' => 7];

$title = ($addon->id) ? "Edit $addon->item" : "New Item";
$url = ($addon->id) ? "/admin/addons/$addon->id" : "/admin/addons";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $addon->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/addons/$addon->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);