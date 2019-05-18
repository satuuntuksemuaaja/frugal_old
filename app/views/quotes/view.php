<?php
use vl\quotes\QuoteGenerator;
$type = ($quote->final) ? "Final Quote" : "Initial Quote";
echo BS::title($type, $quote->lead->customer->name. "'s " . $quote->type);
$pass = true;
$notifications = QuoteGenerator::getNotifications($quote, $pass);

$buttons = [];
$buttons[] = ['icon' => 'file-image-o', 'color' => 'info mjax', 'url' => "/quote/$quote->id/files",
  'text' => 'Drawings/Files',
  'class' => "data-toggle='modal' data-target='#files'"];
 $buttons[] = [
              'icon' => 'money',
              'color' => 'default', 'url' => "/quote/$quote->id/financing",
              'text' => 'Review Financing',
                  ];

if ($pass && $quote->paperwork)
  $buttons[] = ['icon' => 'cloud-download', 'color' => 'info', 'url' => "/quote/$quote->id/contract", 'text' => 'Download PDF'];
if ($quote->final && !$quote->accepted)
 {
    $meta = unserialize($quote->meta);
    if (isset($meta['meta']['finance']))
    $buttons[] = [
                  'icon' => 'check',
                  'color' => 'success', 'url' => "#",
                  'text' => 'Convert Sold',
                  'class' => "data-toggle='confirmation' data-btnOkLabel='Convert to Sold' data-btnOkClass='btn btn-success'
                              data-href='/quote/$quote->id/convert' data-title='This is Sold' data-placement='bottom'"
                  ];


     }
  $buttons[] = [
                  'icon' => 'exclamation',
                  'color' => 'warning', 'url' => "#",
                  'text' => 'Decline/Archive',
                  'class' => "data-toggle='confirmation' data-btnOkLabel='Archive Quote'
                              data-href='/quote/$quote->id/archive' data-title='Confirm Archiving of Quote' data-placement='bottom'"
                  ];
$buttons[] = ['icon' => 'trash-o', 'color' => 'danger',
              'url' => "#",
              'text' => 'Delete Quote',
              'class' => "data-toggle='confirmation' data-btnOkLabel='Delete Quote'
                data-href='/quote/$quote->id/delete' data-title='Are you sure you want to delete this quote?' data-placement='bottom'"
              ];
if (Auth::user()->superuser)
{
  $buttons[] = ['icon' => 'question',
                'color' => 'info',
               'modal' => '#debug',
               'text' => 'Quote Debug'
              ];
  $buttons[] = ['icon' => 'search',
                'color' => 'primary mjax',
                'url' => "/quote/$quote->id/snapshots",
                'text' => 'Snapshots',
                'class' => "data-toggle='modal' data-target='#files'"];

}
if ($quote->final)
{
  $prev = Quote::whereLeadId($quote->lead_id)->whereFinal(0)->first();
  if ($prev)
    $buttons[] = [
                  'icon' => 'arrow-left',
                  'color' => 'success', 'url' => "/quote/$prev->id/view",
                  'text' => 'See Initial',
                  ];
}
else
{
  $final = Quote::whereLeadId($quote->lead_id)->whereFinal(1)->first();
  if ($final)
    $buttons[] = [
                  'icon' => 'arrow-right',
                  'color' => 'success', 'url' => "/quote/$final->id/view",
                  'text' => 'See Final',
                  ];
}

if ($quote->job)
{
   $buttons[] = [
                  'icon' => 'code-fork',
                  'color' => 'info', 'url' => "/job/{$quote->job->id}/schedules",
                  'text' => 'Job Schedule',
                  ];
}




$bar = BS::buttons($buttons);
$bar .= $notifications;
if (Auth::user()->level_id == 7)
{
$buttons = [];
$buttons[] = ['icon' => 'file-image-o', 'color' => 'info mjax', 'url' => "/quote/$quote->id/files",
  'text' => 'Drawings/Files',
  'class' => "data-toggle='modal' data-target='#files'"];
$bar = BS::buttons($buttons);
  echo BS::span(6, $bar);
  echo Modal::init()->id('files')->onlyConstruct()->render();

  return;
}
// Start with Quote Details
$details = new QuoteGenerator($quote, Auth::user()->superuser);
$detailPanel = $details->getQuoteDetails();
$cabinets = $details->getCabinets();
$granite = $details->getGranite();
$appliances = $details->getAppliances();
$additional = $details->getAdditionalInfo();
$questions = $details->getQuestions();
$addons = $details->getAddons();
$payouts = $details->getPayouts();

$total = "<h1>Grand Total: <small> $".number_format($details->total,2) . "</small></h1>";

$left = BS::span(6, $bar.$detailPanel.$cabinets.$granite);
$middle = BS::span(6, $total.$appliances.$additional.$questions.$addons.$payouts);

echo BS::row($left.$middle);

echo Modal::init()->id('files')->onlyConstruct()->render();
echo Modal::init()->id('workModal')->onlyConstruct()->render();
$headers = ['Item', 'Amount', 'Total'];
$rows = $details->debug;
$table = Table::init()->headers($headers)->rows($rows)->render();

echo Modal::init()->id('debug')->header("Quote Debugger")->content($table)->render();

