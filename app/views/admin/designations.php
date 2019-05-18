<?php
$headers = ['Designation', 'Users'];
$rows = [];
$designations = Designation::whereActive(true)->get();
foreach ($designations AS $designation)
  $rows[] = ["<a href='/admin/designations/$designation->id'>$designation->name</a>",
            $designation->users()->count()];
$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);

$designation = (isset($id)) ? Designation::find($id) : new Designation;
$fields = [];
$fields[] = ['type' => 'input', 'var' => 'name', 'text' => 'Designation Name:', 'val' => $designation->name, 'span' => 7];
$fields[] = ['type' => 'input', 'var' => 'override_email', 'text' => 'Designation E-mail Override:',
  'val' => $designation->override_email, 'span' => 7];
$fields[] = ['type' => 'input', 'var' => 'override_sms', 'text' => 'Designation SMS Override:',
  'val' => $designation->override_sms, 'span' => 7, 'mask' => '999.999.9999'];
$title = ($designation->id) ? "Edit $designation->name" : "New Designation";
$url = ($designation->id) ? "/admin/designations/$designation->id" : "/admin/designations";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $designation->id ?
  Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/designations/$designation->id/delete")->render() :
  null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save.$delete)->render();
$span .= BS::span(4, $panel);
echo BS::row($span);
