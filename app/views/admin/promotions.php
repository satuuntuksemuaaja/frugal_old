<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/17/17
 * Time: 9:39 AM
 */
$headers = ['Promotion', 'Type', 'Verbiage', 'Active'];
$rows = [];
foreach (Promotion::all() as $promo)
{
    $rows[] = [
        "<a href='/admin/promotions/$promo->id'>$promo->name</a>",
        $promo->modifier,
        $promo->verbiage,
        $promo->active ? "Yes" : "No"
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();


$span = BS::span(8, $table);
$fields = [];
$promotion = (isset($id)) ? Promotion::find($id) : new Promotion;

$fields[] = ['type' => 'input', 'text' => "Promotion Name:", 'var' => 'name', 'val' => $promotion->name, 'span' => 7];

// Active
$opts = [];
$opts[] = ['val' => 0, 'text' => 'No'];
$opts[] = ['val' => 1, 'text' => 'Yes'];
$fields[] = ['type' => 'select', 'text' => 'Active?', 'opts' => $opts, 'span' => 7, 'var' => 'active'];

//Modifier
$opts = [];
$opts[] = ['val' => 'GRANITE_SQFT', 'text' => 'Granite Price per sqft.'];
$opts[] = ['val' => 'TOTAL_PRICE', 'text' => 'Total Kitchen Price'];
$fields[] = ['type' => 'select', 'text' => 'Modifier', 'opts' => $opts, 'span' => 7, 'var' => 'modifier'];

//Condition
$opts = [];
$opts[] = ['val' => '>', 'text' => '>'];
$opts[] = ['val' => '<', 'text' => '<'];
$opts[] = ['val' => '=', 'text' => '='];
$fields[] = ['type' => 'select', 'text' => 'Condition', 'opts' => $opts, 'span' => 7, 'var' => 'condition'];

$fields[] = ['type' => 'input', 'text' => "Qualifier:", 'var' => 'qualifier', 'val' => $promotion->qualifier, 'span' => 7];

$fields[] = ['type' => 'input', 'text' => "Discount Amount:", 'var' => 'amount', 'val' => $promotion->discount_amount, 'span' => 7];
$fields[] = ['type' => 'textarea', 'text' => 'Contract Verbiage:', 'var' => 'verbiage', 'val' => $promotion->verbiage, 'span' => 7];

$desc = "<p>Example: If you wanted to discount the price per square foot if the price is over 32, then you would set the modifier to 
Granite Price per sqft, condition >, qualifier 32, discount amount 32.";

$title = ($promotion->id) ? "Edit $promotion->name" : "New promotion";
$url = ($promotion->id) ? "/admin/promotions/$promotion->id" : "/admin/promotions";
$form = Forms::init()->id('editForm')->url($url)->labelSpan(4)->elements($fields)->render().$desc;
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('editForm')->render();
$delete = $promotion->id ?
    Button::init()->text("Delete")->icon('trash-o')->color('danger get')->url("/admin/promotions/$promotion->id/delete")->render() :
    null;
$panel = Panel::init('primary')->header($title)->content($form)->footer($save . $delete)->render();
$span .= BS::span(4, $panel);


echo BS::row($span);