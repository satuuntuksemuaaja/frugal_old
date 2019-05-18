<?php
use Carbon\Carbon;
use vl\core\CustomerWidgets;
echo BS::title("Customer Profile", $customer->name);
/*
Profile should have:
Customer Info
Contacts
Leads
Quotes
Jobs
FFT
Warranties
Tasks
Basically everything
 */
$left = null;
$buttons = [];
if ($customer->quotes)
{
  foreach ($customer->quotes AS $quote)
    if ($quote->files)
      $buttons[] = ['icon' => 'file-image-o', 'color' => 'info mjax', 'url' => "/quote/$quote->id/files",
  'text' => "Drawings/Files (Quote: $quote->id)",
  'class' => "data-toggle='modal' data-target='#files'"];
}
$left .= BS::buttons($buttons);

$left .= CustomerWidgets::customerWidget($customer);
$left .= CustomerWidgets::leadWidget($customer);
$left .= CustomerWidgets::quoteWidget($customer);

$right = CustomerWidgets::jobWidget($customer);
$right .= CustomerWidgets::FFTWidget($customer);
$right .= CustomerWidgets::FFTWidget($customer, true);

$righter = CustomerWidgets::notesWidget($customer);
$righter .= CustomerWidgets::tasksWidget($customer);
$left = BS::span(4, $left);
$right = BS::span(4, $right);
$righter = BS::span(4, $righter);

echo BS::row($left.$right.$righter);
echo Modal::init()->id('workModal')->onlyConstruct()->render();
echo Modal::init()->onlyConstruct()->id('files')->render();