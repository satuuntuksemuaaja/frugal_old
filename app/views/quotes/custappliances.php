<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 7/23/17
 * Time: 11:24 AM
 */
echo BS::title("Appliance Configuration", "Enter Brand, Model and Size");
$pre = "<p>Please enter the Brand, Model and Size of each of the appliances listed below.</p>";

$headers = ["Appliance", 'Brand', 'Model', 'Size'];
$rows = [];
$meta = unserialize($quote->meta)['meta'];
$appids = (isset($meta['quote_appliances'])) ? $meta['quote_appliances'] : [];

// First check and make sure we have the records written.
foreach ($appids as $app)
{
    if ($quote->appliances()->whereApplianceId($app)->count() == 0)
        (new QuoteAppliance)->create([
            'quote_id' => $quote->id,
            'appliance_id' => $app
        ]);
}

$fields = [];
foreach ($quote->appliances AS $app)
{
    $rows[] = [
        $app->appliance->name,
        "<input name='app_{$app->id}_brand' value='$app->brand'>",
        "<input name='app_{$app->id}_model' value='$app->model'>",
        "<input name='app_{$app->id}_size' value='$app->size'>",
    ];
}
$start = "<form action='/customer/$quote->id/appliances' id='appform' method='POST'>";
$table = Table::init()->headers($headers)->rows($rows)->render();
$end = "</form>";
$save = Button::init()->text("Save Appliances")->color('primary post pulse-blue')->formid('appform')->icon('save')->render();
$rPanel = Panel::init('info')->header("Enter Appliance Information")->content($start.$pre.$table)->footer("<center>$save</center>")->render();

$right = BS::span(6, $rPanel);
echo BS::row($right);


echo BS::encap("
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");

