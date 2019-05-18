<?php
use vl\quotes\QuoteGenerator;
$headers = ['Designer', 'Client', 'Closed On', 'Installer', 'Plumber', 'Electrician', 'Designer'];
$quotes = Quote::whereAccepted(true)->where('created_at', '>=', '2016-05-01')->orderBy('updated_at', 'DESC')->get();
foreach ($quotes AS $quote)
{
  $details = QuoteGenerator::getQuoteObject($quote);
  $installer = null;
  $plumber = null;
  $designer = null;
  $electrician = null;
  $debugs = $details->debug;
  foreach ($debugs AS $debug)
{
  if (preg_match('/designer/i', $debug[0]))
      $designer .= $debug[0] . " - " . $debug[1]. "<br/>";
    if (preg_match('/electrician/i', $debug[0]))
      $electrician .= $debug[0] . " - " . $debug[1]. "<br/>";
      if (preg_match('/installer/i', $debug[0]))
      $installer .= $debug[0] . " - " . $debug[1]. "<br/>";
    if (preg_match('/plumber/i', $debug[0]))
      $plumber .= $debug[0] . " - " . $debug[1] . "<br/>";
}
if ($quote->lead && $quote->lead->user)
  $rows[] = [$quote->lead->user->name, $quote->lead->customer->name, $quote->updated_at->format("m/d/y"),
    "<h4>$$details->forInstaller</h4>".$installer,
    "<h4>$$details->forPlumber</h4>".$plumber,
    "<h4>$$details->forElectrician</h4>".$electrician,
    "<h4>$$details->forDesigner</h4>".$designer];
}
$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$span = BS::span(12, $table);
echo BS::row($span);

