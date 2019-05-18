<?php
$headers = ['Name', 'Shipping Days', 'Confirmation Days', 'Multiplier', 'Freight', 'Build up'];
$rows = [];
$vendors = Vendor::whereActive(true)->get();
foreach ($vendors AS $vendor)
{
    $wood = $vendor->wood_products ? "<span class='pull-right'><i class='fa fa-exclamation'></i></span>" : null;
    $rows[] = [
        "<a href='/admin/vendors/$vendor->id'>$vendor->name</a>{$wood}",
        $vendor->tts,
        $vendor->confirmation_days,
        $vendor->multiplier,
        $vendor->freight,
        $vendor->buildup];
}

$table = Table::init()->headers($headers)->rows($rows)->render();
$span = BS::span(8, $table);
$vendor = (isset($id)) ? Vendor::find($id) : new Vendor;
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Vendor Name', 'var' => 'name', 'val' => $vendor->name, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Days to Receive', 'var' => 'tts', 'val' => $vendor->tts, 'span' => 5];
$fields[] = ['type' => 'input', 'text' => 'Confirmation Days', 'var' => 'confirmation_days', 'val' => $vendor->confirmation_days, 'span' => 5];
$fields[] = ['type' => 'input', 'text' => 'Multiplier', 'var' => 'multiplier', 'val' => $vendor->multiplier, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Freight', 'var' => 'freight', 'val' => $vendor->freight, 'span' => 7];
$fields[] = ['type' => 'input', 'text' => 'Buildup Cost', 'var' => 'buildup', 'val' => $vendor->buildup, 'span' => 7];
$fields[] = ['type' => 'textarea', 'text' => 'Vendor Colors', 'var' => 'colors', 'val' => $vendor->colors, 'span' => 7];
$opts = [];
$opts[] = ['val' => 'Y', 'text' => 'Allow wood products?', 'checked' => $vendor->wood_products ? true : false];
$fields[] = ['type' => 'checkbox', 'var' => 'wood_products', 'text' => null, 'opts' => $opts, 'span' => 7];

$title = ($vendor->id) ? "Edit $vendor->name" : "New Vendor";
$url = ($vendor->id) ? "/admin/vendors/$vendor->id" : "/admin/vendors";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render();
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $vendor->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/vendors/$vendor->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();
$span .= BS::span(4, $panel);

echo BS::row($span);