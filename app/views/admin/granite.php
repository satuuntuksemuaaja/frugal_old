<?php
$headers = ['Name', 'Price', 'Removal Price'];
$rows = [];
$granites = Granite::orderBy('name', 'ASC')->whereActive(true)->get();
foreach ($granites AS $granite)
    $rows[] = ["<a href='/admin/granite/$granite->id'>$granite->name</a>",
        Editable::init()->id("editableID")->placement('left')->pk($granite->id)->type('text')
                ->title("Set Price")->linkText($granite->price)
                ->url("/admin/granite/$granite->id/price")->render(),
        $granite->removal_price,
    ];

$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(8, $table);

$fields = [];
$granite = (isset($id)) ? Granite::find($id) : new Granite;
$fields[] = ['type' => 'input', 'text' => 'Granite Name:', 'var' => 'name', 'val' => $granite->name, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Price:', 'var' => 'price', 'val' => $granite->price, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Removal Price:', 'var' => 'removal_price', 'val' => $granite->removal_price, 'span' => 7];


$title = ($granite->id) ? "Edit $granite->name" : "New Granite";
$url = ($granite->id) ? "/admin/granite/$granite->id" : "/admin/granite";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $granite->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/granite/$granite->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);