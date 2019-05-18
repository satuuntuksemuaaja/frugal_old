<?php
$headers = ['Extra Line', 'Price', 'Goes To'];
$rows = [];
$extras = Extra::orderBy('name', 'ASC')->whereActive(true)->get();
foreach ($extras AS $extra)
{
    $rows[] = [
        "<a href='/admin/pricing/$extra->id'>{$extra->name}</a>",
        $extra->price,
        $extra->designation ? $extra->designation->name : "Unassigned"
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(8, $table);
$fields = [];
$extra = (isset($id)) ? Extra::find($id) : new Extra;
$fields[] = ['type' => 'input', 'text' => 'Extra Name:', 'var' => 'name', 'val' => $extra->name, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Price:', 'var' => 'price', 'val' => $extra->price, 'span' => 7];
$opts = [];
if ($extra->designation_id)
{
    $opts[] = ['val' => $extra->designation_id, 'text' => Designation::find($extra->designation_id)->name];
}
$opts[] = ['val' => 0, 'text' => '-- Select Designation --'];
foreach (Designation::whereActive(true)->get() as $des)
{
    $opts[] = ['val' => $des->id, 'text' => $des->name];
}
$fields[] = ['type' => 'select', 'text' => 'Money Goes To:', 'var' => 'designation_id', 'opts' => $opts, 'span' => 7];
$title = ($extra->id) ? "Edit $extra->name" : "New extra";
$url = ($extra->id) ? "/admin/pricing/$extra->id" : "/admin/pricing";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $extra->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/pricing/$extra->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);