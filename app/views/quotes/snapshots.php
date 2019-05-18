<?php
$headers = ['Timestamp', 'Contract Link', 'Debug'];
$rows = [];
foreach ($quote->snapshots AS $shot)
{
  $debug = unserialize($shot->debug);
  $headers = ['Item', 'Amount', 'Total'];
  $trow = $debug;
  $table = Table::init()->headers($headers)->clearStyles()->rows($trow)->render();
  $rows[] = [$shot->created_at->format('m/d/y h:i a'), "<a href='/snapshots/$quote->id/$shot->location'>$shot->location</a>",
  $table];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
echo Modal::init()->isInline()->header("Snapshots")->content($table)->render();