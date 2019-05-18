<?php
$fail = false;

switch ($type)
{
   case 'cabinet' :
                $item = JobItem::whereJobId($job->id)->whereInstanceof('Cabinet')->whereReference($reference)->first();
                if (!$item) return "No Cabinets found to order. Either there are no cabinets or you should call Chris";
                $noun = "Cabinet(s)";
                break;
   case 'hardware' : $item = JobItem::whereJobId($job->id)->whereInstanceof('Hardware')->whereReference($reference)->first();
                if (!$item) return "No Hardware found to order. Either there is no hardware or you should call Chris";
                $noun = "Hardware Items";
                break;
   case 'accessory' : $item = JobItem::whereJobId($job->id)->whereInstanceof('Accessory')->whereReference($reference)->first();
                if (!$item) return "No Accessories found to order. Either there are no accessories or you should call Chris";
                $noun = "Accessories";
                break;
}
$state = 'complete';
if ($item->verified == '0000-00-00')
  $state = 'verify';
if ($item->received == '0000-00-00')
  $state = 'receive';
if ($item->confirmed == '0000-00-00')
  $state = 'confirm';
if ($item->ordered == '0000-00-00')
  $state = 'order';

if ($state != 'complete' && $type != 'cabinet')
  {
    $pre = "<h4>You are about to {$state} {$noun}. Click the continue button to confirm.</h4>";
    $save = Button::init()->text(ucFirst($state) . " {$noun}")->color('success mget')->
      icon('check')->url("/job/$job->id/track/$type/reference/$reference/save")->render();
  }
else
{
  $pre = null;
  $save = null;
}

// Show details of what is being ordered.
//
$meta = unserialize($job->quote->meta);
if ($type == 'cabinet')
{
  foreach ($job->quote->cabinets AS $cabinet)
  {
    $item = JobItem::whereInstanceof('cabinet')->whereReference($cabinet->id)->first();
    if (!$item)
    {
      $item = new JobItem;
      $item->instanceof = 'Cabinet';
      $item->reference = $cabinet->id;
      $item->job_id = $job->id;
      $item->save();
    }
    $itemmeta = unserialize($item->meta);
    if (!is_array($itemmeta))
      $itemmeta = [];
    $state = 'complete';
    if ($item->verified == '0000-00-00')
      $state = 'verify';
    if ($item->received == '0000-00-00')
      $state = 'receive';
    if ($item->confirmed == '0000-00-00')
      $state = 'confirm';
    if ($item->ordered == '0000-00-00')
      $state = 'order';
    $save = null;
    if ($state != 'complete')
      $save = Button::init()->text(ucFirst($state) . " ".$cabinet->cabinet->frugal_name)
    ->color('success btn-sm mget')->icon('check')->url("/job/$job->id/track/$type/reference/$cabinet->id/save")
    ->render();
   $pre .= "<h4>" . $cabinet->cabinet->frugal_name."$save</h4>";
   $headers = ['Vendor', 'SKU', 'Description', 'QTY', 'Price', null];
    $rows = [];
    $showOverride = false;
   if ($cabinet->override)
    {
      $cabData = unserialize($cabinet->override);
      $showOverride = BS::callout('danger', "<b>WARNING!</b> This XML file has been overriden from the original contract!");
    }
    else
      $cabData = unserialize($cabinet->data);
    foreach ($cabData AS $idx => $cabitem)
    {
      if (!isset($cabitem['description'])) $cabitem['description'] = null;
      if ($state != 'verify')
        $rows[] = [$cabinet->cabinet->vendor->name,
                  $cabitem['sku'],
                  $cabitem['description'],
                  number_format($cabitem['qty']),
                  "$".number_format($cabitem['price'],2)];
      else
        {
          $verified = !array_key_exists($idx, $itemmeta) ? "<a class='get' href='/item/$item->id/verify/$idx'>Verify</a>" : "Verified";
          if ($verified != 'Verified') $fail = true;
          $rows[] = [$cabinet->cabinet->vendor->name,
                  $cabitem['sku'],
                  $cabitem['description'],
                  number_format($cabitem['qty']),
                  "$".number_format($cabitem['price'],2),
                  $verified];
        }
    }

    $pre .= $showOverride;
    $pre .= Table::init()->headers($headers)->rows($rows)->render();
  }
  $save = null;
}
if ($type == 'hardware')
{
  $itemmeta = unserialize($item->meta);
    if (!is_array($itemmeta))
      $itemmeta = [];
  $rows = [];
  if (isset($meta['meta']['quote_pulls']))
    foreach($meta['meta']['quote_pulls'] as $pl => $qty)
      {
        $verified = !array_key_exists($pl, $itemmeta) ? "<a class='get' href='/item/$item->id/verify/$pl'>Verify</a>" : "Verified";
        //if ($verified != 'Verified' && $state == 'verify') $fail = true;
        $hardware = Hardware::find($pl);
        if (!$hardware) continue;
        if ($state == 'verify')
          $rows[] = [
          $hardware->vendor->name,
          'Pulls',
          $hardware->sku,
          $hardware->description,
          $qty,
          $verified];
        else
          $rows[] = [
          $hardware->vendor->name,
          'Pulls',
          $hardware->sku,
          $hardware->description,
          $qty];
      }
  if (isset($meta['meta']['quote_knobs']))
    foreach($meta['meta']['quote_knobs'] as $pl => $qty)
      {
        $hardware = Hardware::find($pl);
        $verified = !array_key_exists($pl, $itemmeta) ? "<a class='get' href='/item/$item->id/verify/$pl'>Verify</a>" : "Verified";
        //if ($verified != 'Verified' && $state == 'verify') $fail = true;
        if (!$hardware) continue;
        $rows[] = [
        $hardware->vendor->name,
        'Knobs',
        $hardware->sku,
        $hardware->description,
        $qty, $verified];
      }
      $headers = ['Vendor', 'Type','SKU','Description','QTY', null];
      $pre .= Table::init()->headers($headers)->rows($rows)->render();
}

if ($type == 'accessory')
{
  $itemmeta = unserialize($item->meta);
    if (!is_array($itemmeta))
      $itemmeta = [];
  $rows = [];
  if (isset($meta['meta']['quote_accessories']))
  foreach($meta['meta']['quote_accessories'] as $acc => $qty)
  {
    $verified = !array_key_exists($acc, $itemmeta) ? "<a class='get' href='/item/$item->id/verify/$acc'>Verify</a>" : "Verified";
    //if ($verified != 'Verified' && $state == 'verify') $fail = true;
    $accessory = Accessory::find($acc);
    if (!$accessory) continue;
    if ($state != 'verify')
      $rows[] = [
                $accessory->vendor->name,
                $accessory->sku,
                $accessory->description,
                $qty];
    else
      $rows[] = [
                $accessory->vendor->name,
                $accessory->sku,
                $accessory->description,
                $qty,
                $verified];
  }
      $headers = ['Vendor', 'SKU', 'Decripton', 'QTY', null];
      $pre .= Table::init()->headers($headers)->rows($rows)->render();
}
if (!$job->has_money)
{
  $pre = BS::callout('danger', "<b>Unable to Track Item</b> This system believes that the money has not been received to
    begin order parts for this job. Please click the <i class='fa fa-exclamation-circle'></i> icon on the job board to
    enable this feature.");
  $save = null;
  echo Modal::init()->isInline()->header(ucFirst($state) . " {$noun}")->content($pre)->footer($save)->render();
}
if ($fail) $save = null;
echo Modal::init()->isInline()->header(ucFirst($state) . " {$noun}")->content($pre)->footer($save)->render();