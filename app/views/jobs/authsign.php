<?php
use Carbon\Carbon;
use vl\core\Signature;
$auth = $job->authorization;
if (!isset($raw))
{
    echo BS::title("Customer Signature", "Confirm Job Authorizations");
}
    $pre = "<h1>Frugal Kitchens Job Authorization Request</h1>
<p class='lead'>I, {$job->quote->lead->customer->name}, hereby confirm that as of " .
        Carbon::now()->format("m/d/y h:i a") . ", I authorize the special items listed below to be handled by Frugal Kitchens. </p>

";


if ($auth->signature)
{
    $w = 598;
    $h = 155;
    $link = (!isset($raw)) ? "You can download the <a href='/job/{$job->id}/auth/pdf'>pdf here</a>" : null;
    if (!isset($raw))
    {
        $pre .= BS::callout('info', "<b>Signature Found</b> A signature was found for this authorization and was
    signed by {$job->quote->lead->customer->name} on " . Carbon::parse($auth->signed_on)->format('m/d/y h:i a') . ". If
    additional items were found and are to be completed under the initial agreement, the customer can sign again and the
    items to this date will be added to the contract. {$link}");
    }
    $img = Signature::sigJsonToImage($auth->signature, array('imageSize' => array($w, $h), 'bgColour' => 'transparent'));
    ob_start();
    imagepng($img);
    imagedestroy($img);
    $img = base64_encode(ob_get_clean());
    $pre .= '<div style="width:475px;">
           <p class="drawItDesc" style="display: block;">Signed By:</p>
            <img src="data:image/png;base64,' . $img . '" />
            <p style="border-top:1px solid gray; padding-top:10px; text-align:center;">' . $job->quote->lead->customer->name . '</p>
            </div>';

//
//


}
$headers = ['Item'];
$rows = [];
foreach ($auth->items AS $item)
{
    $rows[] = [$item->item];
}

$table = Table::init()->headers($headers)->rows($rows)->width(100)->render();
$sig = '
<center>
<form method="post" action="/job/' . $job->id . '/authsign" class="sigPad">
  <p class="drawItDesc">Draw signature below</p>
  <ul class="sigNav" style="list-style:none;">

    <li class="clearButton"><a href="#clear">Clear</a></li>
  <div class="sig sigWrapper">
    <div class="typed"></div>
    <canvas class="pad" width="598" height="155"></canvas>
    <input type="hidden" name="output" class="output">
  </div>
  <button type="submit">I approve of these requests.</button>


</form></center>
';
if (isset($raw)) $sig = null;
$span = BS::span(8, $pre . $table . $sig, 2);
echo BS::row($span);

echo BS::encap("$('.sigPad').signaturePad({drawOnly:true});
$.fn.signaturePad.clear = '.clearButton';
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");