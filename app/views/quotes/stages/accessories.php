<?php
echo BS::title("Accessories", $quote->lead->customer->name);
$meta = unserialize($quote->meta);
$meta = $meta['meta'];

// Accessories
$headers = ['QTY', 'SKU', 'Description', 'Price'];
$accessoriesStore = (isset($meta['quote_accessories'])) ? $meta['quote_accessories'] : [];
$rows = [];
$form = "<form id='accessoriesForm' method='post' action='/quote/$quote->id/accessories'>";
$accessories = Accessory::orderBy('name', 'ASC')->get();
foreach ($accessories AS $accessory)
{
  $value = (isset($accessoriesStore[$accessory->id])) ? $accessoriesStore[$accessory->id] : 0;
  $rows[] = ["<input type='input' name='acc_$accessory->id' value='$value'>",
      $accessory->sku,
      $accessory->description,
      "$".number_format($accessory->price,2)
  ];
}
$table = $form.Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$save = Button::init()->text("Save Accessories")->color('danger post pulse-red')->formid('accessoriesForm')->icon('save')->render();
$accessoriesPanel = Panel::init('info')->header("Accessories")->content($table)->footer("<center>$save</center></form>")->render();
$all = BS::span(6, $accessoriesPanel);

// Tabled
if (isset($meta['quote_accessories']) && $meta['quote_accessories'])
  {
    $headers = ['Item', "Qty", 'Delete'];
    $rows = [];
    foreach ($meta['quote_accessories'] AS $idx => $acc)
    {
      $accessory = Accessory::find($idx);
      $rows[] = [$accessory->sku, $acc, "<a class='get' href='/quote/$quote->id/accessory/$idx/delete'>x</a>"];
    }
    $table = Table::init()->headers($headers)->rows($rows)->render();
    $all .= BS::span(6, "<h4>Accesories In Quote</h4>".$table);
  }

echo BS::row($all);


$pass = true;
if (!isset($meta['progress_accessories'])) $pass = false;
$options = "<div class='btn-group'>";
$options .= Button::init()->text("Review Appliances")->color('warning btn-lg')->withoutGroup()->icon('arrow-left')
  ->url("/quote/$quote->id/appliances")->render();

if ($pass)
  $options .= Button::init()->text("Next")->color('success btn-lg ')->withoutGroup()->icon('arrow-right')
  ->url("/quote/$quote->id/hardware")->render();

$options .= Button::init()->text("Quote Overview")->color('info btn-lg')->withoutGroup()->icon('share')
  ->url("/quote/$quote->id/view")->render();
$options .= "</div>";


echo BS::row(BS::span(9, $options, 3));