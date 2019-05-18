<?php
use Carbon\Carbon;
use vl\core\Signature;

if (!isset($raw))
  echo BS::title("Customer Signature", "Confirm Change Order Request");
$pre = "<h1>Change Order #{$order->id} for {$order->job->quote->lead->customer->name}</h1>
<p class='lead'>I, {$order->job->quote->lead->customer->name}, hereby confirm that as of ".
Carbon::now()->format("m/d/y h:i a")." the items listed below have been agreed upon to be added/modified by Frugal Kitchens
and Cabinets for my job. By signing this agreement, I understand that I am agreeing to pay for, in full, the items listed below. </p>
<p class='lead'><b>Note:</b> 50% of the change order total(s) is due before work begins.</p>";


if ($order->signature)
  {
    $w = 598;
    $h = 155;
    $link = (!isset($raw)) ? "You can download the <a href='/change/{$order->id}/signature/pdf'>pdf here</a>" : null;
    $pre .= BS::callout('info', "<b>Signature Found</b> A signature was found for this change order, and was
    signed by {$order->job->quote->lead->customer->name} on " . Carbon::parse($order->signed_on)->format('m/d/y h:i a') . ". If
    additional items need to be added, please create a new change order request.");
    $img = Signature::sigJsonToImage($order->signature, array('imageSize'=>array($w, $h),'bgColour' => 'transparent'));
    ob_start();
    imagepng($img);
    imagedestroy($img);
    $img=base64_encode(ob_get_clean());
    $pre .= '<div style="width:475;">
           <p class="drawItDesc" style="display: block;">Signed By:</p>
            <img src="data:image/png;base64,'.$img.'" />
            <p style="border-top:1px solid gray; padding-top:10px; text-align:center;">'.$order->job->quote->lead->customer->name.'</p>
            </div>';
}
$headers = ['Item',  'Price'];
$rows = [];
$total = 0;
foreach ($order->items AS $item)
 {
   $rows[] = [$item->description, "$".number_format($item->price,2)];
   $total += $item->price;
 }

$table = Table::init()->headers($headers)->rows($rows)->width(100)->render();
/*
$snapshot = Snapshot::whereQuoteId($order->job->quote_id)->first();
if (!$snapshot) // No Snapshot use actual quote.
  $snapshot = Quote::find($order->job->quote_id);
if ($snapshot instanceof Quote)
  {
    $snapshot = vl\quotes\QuoteGenerator::getQuoteObject($snapshot);
    $qtotal = $snapshot->total;
  }
  else
  {
    $snapshot = unserialize($snapshot->debug);
    foreach ($snapshot as $item)
      $qtotal = $item[2];
    $qtotal = preg_replace('/,/', null, $qtotal);

  }
 */
//$quoteo = vl\quotes\QuoteGenerator::getQuoteObject($order->job->quote);
//$meta = unserialize($order->job->quote->meta)['meta'];
//$qtotal = vl\quotes\QuoteGenerator::financing($order->job->quote, $quoteo, true);
//$text =  vl\quotes\QuoteGenerator::financing($order->job->quote, $quoteo);
//$qtotal = $quoteo->total;
//$qtotal = $qtotal + ($qtotal * .05);

//$qtotal = $qtotal - $meta['quote_coupon'];



//$newtotal = $total + $qtotal;
$sig =  '
<center>
<h3>Change Order Total: $'.number_format($total,2).'</h3>
<a href="/change/'.$order->id.'/decline" class="btn btn-danger btn-lg"><i class="fa fa-times"></i> Decline Change Order</a>
<form method="post" action="/change/'.$order->id.'/signature" class="sigPad">
  <p class="drawItDesc">Draw signature below</p>
  <ul class="sigNav" style="list-style:none;">

    <li class="clearButton"><a href="#clear">Clear</a></li>
  <div class="sig sigWrapper">
    <div class="typed"></div>
    <canvas class="pad" width="598" height="155"></canvas>
    <input type="hidden" name="output" class="output">
  </div>
  <button type="submit">I accept the terms of this agreement.</button>


</form></center>
';
if (isset($raw)) $sig = null;
$span = BS::span(8, $pre.$table.$sig, 2);
echo BS::row($span);

echo BS::encap("$('.sigPad').signaturePad({drawOnly:true});
$.fn.signaturePad.clear = '.clearButton';
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");