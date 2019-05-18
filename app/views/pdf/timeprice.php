<?php
use vl\quotes\QuoteGenerator;

$meta = unserialize($quote->meta)['meta'];
$details = QuoteGenerator::getQuoteObject($quote);
$data = "<div class='page'><center><img src='".public_path()."/logo.png'/></center>";
if ($quote->type == 'Full Kitchen')
{
    $data .= "<h3>TIME FOR STARTING and COMPLETING PROJECT:</h3>";

    $data .= "
<span style='text-transform: uppercase; font-weight:bold'>1. Cabinets will take approximately four to six weeks to be delivered to Frugal Kitchens & Cabinets starting after phone review with Frugal Representative
 and is subject to the availability of the cabinets from the supplier. If manufacturer informs Frugal Kitchens
of any unforeseen delays in delivery, we will notify you at that time. </span><br/>
2. Once cabinets have arrived, Frugal Kitchens will contact you to schedule installation date.<br/>
3. Frugal Kitchens and Cabinets will get you a working kitchen within 5 business days.*
<br/
If any parts are needed we will order them during the walk-through. As soon as we receive them we will call to set up a day to complete the project.
<br/>
* Does not include any added work, unforeseen delays, delays by the manufacturer or acts of God.";
}


$finance = (isset($meta['finance'])) ? $meta['finance'] : null;
$data .= "<h3>AGREEMENT PRICE and PAYMENT:</h3>";

$promo = null;
if ($quote->promotion)
{
    $promo = "This price also includes a promotion: <span style='text-decoration:underline'>{$quote->promotion->verbiage}</span>";
}
if (isset($meta['quote_coupon']) && $meta['quote_coupon'])
{
    $data .= "The cost of this order is $" . number_format($details->total + $meta['quote_coupon'], 2) .
        ". We are applying your coupon in the amount of $" . number_format($meta['quote_coupon'], 2) . " to this order making
        the total: <b>$" . number_format($details->total, 2) . ".</b> This price includes sales tax.</b> {$promo} <br/>";
}
else
{
    $data .= "The cost of this order is $" . number_format($details->total, 2) . ". This price includes sales tax. {$promo} <br/>";
}


if (isset($finance) && is_array($finance))
{ // is finance
    $data .= QuoteGenerator::financing($quote, $details);
} // if financing
else
{
    $data .= "<h3>Frugals Flexible Payment Options:</h3>";
    $fin12 = $details->total * .035;
    $fin65 = $details->total * .02;
    $fin84 = $details->total * .0173;
    // Take away the 5%
    $fivePercent = $details->total * .05;
    $allCash = $details->total - $fivePercent;

    // Take away 2.5%
    $twoPointFive = $details->total * .025;
    $allCredit = $details->total - $twoPointFive;

    // No financing options
    $nof_firstPayment = $details->total * .50;
    $nof_secondPayment = $details->total * .45;
    $nof_finalPayment = $details->total - ($nof_firstPayment + $nof_secondPayment);

    $data .= "
        Option A: 0% interest for 12 months (Wells Fargo)   - $" . number_format($fin12, 2) . " per month.<br/>
        Option B: 9.9% interest for 65 months (Wells Fargo) - $" . number_format($fin65, 2) . " per month.<br/>";

    if ($quote->final)
        $data .= "Option C: 0% no payments no interest for 12 months (GreenSky Financial) - $0.00 per month.<br/>
        Option D: 9.9% interest for 84 months (GreenSky Financial) - $". number_format($fin84,2) . " per month.<br/>";
    
}

$data .= "<h3>TERMS and CONDITIONS</h3>";
$ts = time();
$expire = $ts + (86400 * 60); // Expire in 45 days
$expire = date("m/d/y", $expire);
$data .= "The Terms and Conditions are expressly incorporated into this Agreement. This Agreement
  constitutes the entire understanding of both parties. No other understanding or representations, verbal or otherwise,
  shall be binding unless in writing and signed by both the BUYER/OWNER and Frugal Kitchens.
<br/>
<b>Notice to the BUYER/OWNER - This agreement shall not become effective or binding until signed by BUYER/OWNER and Frugal Kitchens.</b>
<br/>
1. Do not sign this Agreement before you read it.<br/>
2. Do not sign this Agreement if it contains any blank spaces.<br/>
3. BUYER/OWNER is entitled to a completely filled in copy of the Agreement and acknowledges that he/she has read and
received a legible copy of the Agreement signed by Frugal Kitchens which includes all the Terms and Conditions before any
of the work on the project can be started.<br/>
4. Frugal Kitchens will only complete work that is documented in this contract or noted on signed drawings.<br/>
5. Frugal Kitchens orders cabinets which cannot be returned or cancelled once the order is placed. Other provisions of
this contract notwithstanding, including but not limited to the ultimate availability of any third-party financing,
Buyer/Owner agrees to fully pay 50% of the quoted price for any such cabinets once such order is placed by Frugal Kitchens.<br/>
6. Frugal Kitchens wants to ensure that you get everything that was discussed and that we have all the information required. So a
Frugal Representative will contact you after this contract is signed and review your options and our procedures. NOTHING will be ordered until this
recorded conversation between Buyer/Owner and a Frugal Representative is complete. After the review is compleleted your 4-6 week timeline will begin.";

$data .= "</div>";
echo $data;