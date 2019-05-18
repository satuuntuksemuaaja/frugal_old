<?php
echo BS::title("Hardware", $quote->lead->customer->name);
$meta = unserialize($quote->meta);
$meta = $meta['meta'];

// Pulls

// Tabled
if (isset($meta['quote_pulls']) && $meta['quote_pulls'])
{
    $headers = ['Item', "Qty", 'Delete'];
    $rows = [];
    foreach ($meta['quote_pulls'] AS $pull => $qty)
    {
        $hardware = Hardware::find($pull);
        $rows[] = [$hardware->sku, $qty, "<a class='get' href='/quote/$quote->id/hardware/pull/$pull/delete'>x</a>"];
    }
    $table = Table::init()->headers($headers)->rows($rows)->render();
    $pullsTable = BS::span(6, "<h4>Pulls In Quote</h4>" . $table);
}
else $pullsTable = null;
$headers = ['QTY', 'SKU', 'Description'];
$rows = [];
$pullStore = (isset($meta['quote_pulls'])) ? $meta['quote_pulls'] : [];
$rows = [];
$form = "<form id='pullsForm' method='post' action='/quote/$quote->id/hardware'>";
$pulls = Hardware::where('description', 'like', '%pull%')->orderBy('sku', 'ASC')->get();
foreach ($pulls AS $pull)
{
    $value = (isset($pullStore[$pull->id])) ? $pullStore[$pull->id] : 0;
    $rows[] = ["<input type='input' name='pull_$pull->id' value='$value'>",
        $pull->sku,
        $pull->description
    ];
}
$table = $form . Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$table .= "<input type='hidden' name='pulls' value='Y'>";
$save = Button::init()->text("Save Pulls")->color('danger post pulse-red')->formid('pullsForm')->icon('save')->render();
$pullsPanel = Panel::init('info')->header("Select Pulls")->content($pullsTable . $table)->footer("<center>$save</center></form>")->render();


// Tabled
if (isset($meta['quote_knobs']) && $meta['quote_knobs'])
{
    $headers = ['Item', "Qty", 'Delete'];
    $rows = [];
    foreach ($meta['quote_knobs'] AS $knob => $qty)
    {
        $hardware = Hardware::find($knob);
        $rows[] = [$hardware->sku, $qty, "<a class='get' href='/quote/$quote->id/hardware/knob/$knob/delete'>x</a>"];
    }
    $table = Table::init()->headers($headers)->rows($rows)->render();
    $knobsTable = BS::span(6, "<h4>Knobs In Quote</h4>" . $table);
}
else $knobsTable = null;
// Knobs
$headers = ['QTY', 'SKU', 'Description'];
$rows = [];
$knobStore = (isset($meta['quote_knobs'])) ? $meta['quote_knobs'] : [];
$rows = [];
$form = "<form id='knobsForm' method='post' action='/quote/$quote->id/hardware'>";
$knobs = Hardware::where('description', 'like', '%knob%')->orderBy('sku', 'ASC')->get();
foreach ($knobs AS $knob)
{
    $value = (isset($knobStore[$knob->id])) ? $knobStore[$knob->id] : 0;
    $rows[] = ["<input type='input' name='knob_$knob->id' value='$value'>",
        $knob->sku,
        $knob->description
    ];
}
$table = $form . Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$table .= "<input type='hidden' name='knobs' value='Y'>";
$save = Button::init()->text("Save Knobs")->color('danger post pulse-red')->formid('knobsForm')->icon('save')->render();
$knobsPanel = Panel::init('info')->header("Select Knobs")->content($knobsTable . $table)->footer("<center>$save</center></form>")->render();

$info = "<div class='alert alert-info'>To add a location to your knobs or pulls add a colon after the quantity. For instance: 1:Some Place</div>";
$left = BS::span(6, $info.$pullsPanel);
$right = BS::span(6, $knobsPanel);
echo BS::row($left . $right);


$pass = true;
if (!isset($meta['progress_knobs']) || !isset($meta['progress_pulls'])) $pass = false;
$options = "<div class='btn-group'>";
$options .= Button::init()->text("Review pulls")->color('warning btn-lg')->withoutGroup()->icon('arrow-left')
                  ->url("/quote/$quote->id/pulls")->render();

if ($pass)
{
    $options .= Button::init()->text("Next")->color('success btn-lg ')->withoutGroup()->icon('arrow-right')
                      ->url("/quote/$quote->id/additional")->render();
}

$options .= Button::init()->text("Quote Overview")->color('info btn-lg')->withoutGroup()->icon('share')
                  ->url("/quote/$quote->id/view")->render();
$options .= "</div>";


echo BS::row(BS::span(9, $options, 3));
