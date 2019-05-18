<?php
function cabinets($quote)
{
    $data = null;
    foreach ($quote->cabinets AS $cabinet)
    {
        if ($cabinet->cabinet)
        {
            $data .= "<tr><td>Cabinet Type</td><td align='center'>{$cabinet->cabinet->frugal_name}</td></tr>";
        }
        $color = $cabinet->color ?: $cabinet->cabinet->description;
        $data .= "<tr><td>Cabinet Color</td><td align='center'>{$color}</td></tr>";
    }
    return $data;
}

function hardware($quote)
{
    $meta = unserialize($quote->meta)['meta'];
    $hwids = (isset($meta['quote_pulls'])) ? $meta['quote_pulls'] : [];
    $field = null;
    $hwttl = 0;
    $hwmax = 3.00;
    $data = null;
    $data .= "<tr>
            <td>Hardware</td><td align='center'>";
    foreach ($hwids AS $hw => $qty)
    {
        $hardware = Hardware::find($hw);
        if ($hardware && $hardware->price > $hwmax)
        {
            $sub = $hardware->price - $hwmax;
            $hwttl += ($sub * $qty);
        }
        if ($hardware)
        {
            $data .= $hardware->sku . " x $qty, ";
        }
    }
    if (empty($hwids))
         $data .= "<b>No Hardware Found - Install date cannot be scheduled until this is complete.</b>";
    $data .= "</td></tr><tr><td>Knobs</td><td align='center'>";
    // Knobs
    $hwids = (isset($meta['quote_knobs'])) ? $meta['quote_knobs'] : [];
    $field = null;
    foreach ($hwids AS $hw => $qty)
    {
        $hardware = Hardware::find($hw);
        if ($hardware && $hardware->price > $hwmax)
        {
            $sub = $hardware->price - $hwmax;
            $hwttl += ($sub * $qty);
        }
        if ($hardware)
        {
            $data .= $hardware->sku . " x $qty, ";
        }

    }
    $data .= "</td></tr>";
    return $data;
}

/**
 * @param $quote
 * @return null
 */
function counters($quote)
{
    $counters = null;
    foreach ($quote->granites AS $granite)
    {
        $counters .= "Type: ";
        $counters .= $granite->granite ? $granite->granite->name : $granite->granite_override;
        $counters .= " (";
        $counters .= $granite->granite && !$granite->granite_override ? "$" . number_format($granite->granite->price,2) : "$". number_format($granite->pp_sqft,2);
        $counters .= " p/sqft) <br/>";
        $counters .= "Edge: $granite->counter_edge<br/>";
    }



    $tile = null;

    if (isset($meta['quote_tile_feet']) && $meta['quote_tile_feet'] > 0)
    {
        $in = $meta['quote_tile_feet'] * 12;
        $calc1 = ($in * $meta['quote_tile_backsplash']) / 144;

        if ($meta['quote_tile_pattern'] == 'Pattern')
        {
        } // Add $100 if a pattern.
        if ($meta['quote_tile_sealed'] == 'Yes')
        {
            $sealed = "Yes";
        }
        else
        {
            $sealed = "No";
        }


        $tile .= "<tr><td>Tile Pattern</td><td align='center'>$meta[quote_tile_pattern]</td></tr>
                   <tr><td>Tile Backsplash</td><td align='center'>$calc1 ft.</td></tr>
                  <tr><td>Tile Sealed</td><td align='center'>$sealed</td></tr>";
    }
    // Markup
    $counters = "<tr><td>Counter Selection(s)</td><td align='center'>$counters</td></tr>";
    return $counters . $tile;
}
function accessories($quote)
{
    $data = null;
    $meta = unserialize($quote->meta)['meta'];
    $accids = (isset($meta['quote_accessories'])) ? $meta['quote_accessories'] : [];
    $accData = null;

    foreach ($accids AS $acc => $qty)
    {
        $acc = Accessory::find($acc);
        if (!$acc)
            continue;
        $accData .= $acc->name . " x {$qty}, ";
    }
    if ($accData)
    {
        $accData = substr($accData, 0, -2);
        return "<tr><td>Accessories:</td><td>$accData</td></tr>";
    }

}

function sinkAppliances($quote)
{
    $data = null;
    $meta = unserialize($quote->meta)['meta'];
    if (isset($meta['sinks']))
    {
        foreach ($meta['sinks'] AS $sink)
        {
            if (!$sink)
            {
                continue;
            }

            $sink = Sink::find($sink);

            $sink_type = ($sink) ? $sink->name : 'None';
            $data .= "<tr><td>Sink</td><td align='center'>$sink_type</td></tr>";
        }
    }

    $appids = (isset($meta['quote_appliances'])) ? $meta['quote_appliances'] : [];
    $field = null;
    $data .= "<tr><td>Appliances</td><td align='center'>";
    if ($appids)
    {
        foreach ($appids AS $app)
        {
            $appliance = Appliance::find($app);
            $data .= $appliance->name . ", ";
        }
        $data .= "</td></tr>";
    }
    $q = null;
    $questions = Question::whereContract(1)->get();
    foreach ($questions AS $question)
    {
        foreach ($quote->answers AS $answer)
        {
            if (!$answer->question || !$answer->question->active) continue;
            if ($answer->question_id == $question->id && ($answer->answer > 0 || $answer->answer == 'on' || $answer->answer == 'Y'))
                $q .= "* " . sprintf($question->contract_format, $answer->answer)."<br/>";
        }
    }
if (isset($meta['quote_special']) && $meta['quote_special'])
    $data .= "<tr>
<td>Special Instructions</td><td align='center'>".nl2br($meta['quote_special'])." <br/>$q</td></tr>";


    return $data;
}
//<img src='".asset('logo.png')."' alt='' /> ";
use vl\core\Formatter;

$contact = $quote->lead->customer->contacts()->first();
$number = Formatter::numberFormat($contact->home);
$mobile = Formatter::numberFormat($contact->mobile);
$obj = \vl\quotes\QuoteGenerator::getQuoteObject($quote);
$data = "<style>
body {
font-family: 'Verdana';
                font-size:14px;
                }
        .page {
                page-break-before: always;
                font-family: 'Verdana';
                font-size:14px;
              }
        .footer { position: fixed;
                  bottom: 0px;

        }
      .pagenum:before { content: counter(page); }
    </style>
</style>
<center><img src='/web/frugalk/frugalk.com/public/logo.png'/>


<h1 style='border:2px solid #000000'>Job Summary for {$quote->lead->customer->name} <br/> </h1>
<table border='0' width='100%'>
". cabinets($quote) .     counters($quote) ."
<tr style='background: #dedede'>
    <td colspan=2>Slab chosen by customer, make sure they complete the granite selection sheet on Page 17. Email it to counters@frugalkitchens.com or
    fax to 770.631.9853. Customer declines slab selection (if they don't choose above option)</td>
</tr>
<tr style='background: #efefef'>
<td colspan=2>
Permission to continue the job up to 5 sq ft of counters without being notified till the completion of the job. If it exceeds 5 sq ft of counter, customer needs
to be notified, remind them that we need a good contact number so we can get coverage (if applicable) approved. Remind customer we need a signed change order 
before we proceed. This could effect five day install if we can't reach the customer.
</td>
</tr>
". sinkAppliances($quote) .  hardware($quote) . accessories($quote) . "
<tr>
    <td>Total Contract Price</td> <td align='center'>$" . number_format($obj->total,2) . "</td>
</tr>

<tr>
    <td>Payment Schedule</td> <td align='center'>" . str_replace("<br/><br/>", "<br/>", \vl\quotes\QuoteGenerator::financing($quote, $obj, false, true)) . "</td>
</tr>

<tr style='background: #efefef'>
    <td colspan=2>Cabinets can take up to <b>4 to 6 weeks</b> to before they are received. Frugal Kitchens will call you as soon as they arrive.</td>
</tr>
<tr style='background: #dedede'>
    <td colspan=2>Customer <b>must</b> be present during the walk-through. After the walk-through is complete, Frugal Kitchens will order any missing 
    or defective parts and could take up to six weeks depending on the manufacturer.</td>
</tr>
<tr style='background: #efefef'>
    <td colspan=2>Please ensure customer fills out the granite selection sheet and emails to counters@frugalkitchens.com or fax to 770.631.9853 if they 
    plan on picking out slabs at one of our preferred vendors.</td>
</tr>

<tr style='background: #dedede'>
    <td colspan=2>On <b>day one</b> we will be dropping off a trailer and it will be in customer's driveway for most of the week. If they want the trailer 
    picked up the same day, there is a $250.00 charge to send an employee out to pick up the trailer after hours.</td>
</tr>


</table>

<hr/>
<table border=0 cellpadding=10 width='98%' style='background: #efefef'>
<tr>
<td width=\"50%\"><b>Date Review Completed</b> <br/><br/>_______________________________<br/><Br/><br/></td>

</tr>
<tr>
<td><b>Frugal Rep (Print): _________________________ </b></td>    <td> <b>Frugal Rep Signature: __________________________</b></td>
</tr>
</table>
";
echo $data;

