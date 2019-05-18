<?php
$headers = ['Item'];
$rows = [];
$authorizations = AuthorizationList::whereActive(true)->get();
foreach ($authorizations AS $authorization)
{
    $rows[] = [
        "<a href='/admin/authorizations/$authorization->id'>$authorization->item</a>",
        
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);
$fields = [];
$authorization = (isset($id)) ? AuthorizationList::find($id) : new AuthorizationList;
$fields[] = ['type' => 'textarea', 'text' => 'Item:', 'var' => 'item', 'val' => $authorization->item, 'span' => 7];

$title = ($authorization->id) ? "Edit $authorization->item" : "New Item";
$url = ($authorization->id) ? "/admin/authorizations/$authorization->id" : "/admin/authorizations";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $authorization->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/authorizations/$authorization->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);