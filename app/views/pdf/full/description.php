<?php
$meta = unserialize($quote->meta)['meta'];

$data = "<center><h3>DESCRIPTION OF PROJECT</h3></center>
Frugal Kitchens & Cabinets will construct the improvements described in this Agreement generally
as follows and within industry standards: 
<table border='0' cellspacing='0' cellpadding='2' width='100%'>";

// Cabinets
$data .= "
<tr bgcolor='#5f9ea0' style='color: #fff'>
    <td align='center'><b>Cabinets</b></td>
    <td align='center'><b>Description</b></td>
    <td align='center'><b>Color</b></td>
    <td align='center'><b>Image</b></td>
   
</tr>";
foreach ($quote->cabinets AS $cabinet)
{
    $color = $cabinet->color ?: $cabinet->cabinet->description;

    if ($cabinet->cabinet->image)
    {
        $figure = $cabinet->id;
    }
    else $figure = null;
    if ($cabinet->customer_removed) $cabinet->description .= "<br/><small>NOTICE!: customer has elected to remove and dispose of cabinets 
from job before Frugal Kitchens & Cabinets starts kitchen installation. If any cabinets are not taking down Frugal Kitchens & Cabinets will 
remove at a cost of $100.00 a cabinet or $500.00 whichever is less. This may also delay your install depending on the size of the project. </small>";
    else $cabinet->description .= "<br/><small> Frugal Kitchens & Cabinets will remove and dispose of cabinets and appliances that are being replaced on 
this project. This is not included in 5 day install and an added day will be needed to complete.</small>";
    $data .= "
    <tr>
        <td align='center'>{$cabinet->cabinet->frugal_name}</td>
        <td align='center'>{$cabinet->description}</td>
        <td align='center'>{$color}</td>
        <td align='center'>{$figure}</td>

    </tr>";
}
if ($quote->type != 'Cabinet and Install' && $quote->type != 'Cabinet Only')
{
    $data .= "<tr bgcolor='#5f9ea0' style='color: #fff'>
    <td align='center'><b>Granite</b></td>
    <td align='center'><b>Location</b></td>
    <td align='center'><b>Price p/sqft</b></td>
    <td align='center'><b>Edge</b></td>
</tr>";
    foreach ($quote->granites as $granite)
    {
        $data .= "
        <tr>
        <td align='center'>";
        $data .= $granite->granite && !$granite->granite_override ? $granite->granite->name : $granite->granite_override;
        $data .= "</td>    
    <td align='center'>{$granite->description}</td>
    <td align='center'>$granite->sqft sqft @ ";
        $data .= $granite->granite && !$granite->granite_override ? "$" . number_format($granite->granite->price,
                2) : "$" . number_format($granite->pp_sqft, 2);
        $data .= "</td>
    <td align='center'>{$granite->counter_edge}</td>
    </tr>
    ";
    }
}

// Sinks
if ($quote->type != 'Cabinet and Install' && $quote->type != 'Cabinet Small Job')
{
    $data .= "<tr bgcolor='#5f9ea0' style='color: #fff'>
    <td align='center'><b>Sinks</b></td>
    <td align='center'><b>Material</b></td>
    <td align='center'><b></b></td>
    <td align='center'><b></b></td>
</tr>";

    if (isset($meta['sinks']))
    {
        if (!isset($meta['sink_plumber']))
        {
            $meta['sink_plumber'] = [];
        }
        foreach ($meta['sinks'] AS $sink)
        {
            if ($sink)
            {
                $data .= "<tr>";
                $data .= "<td align='center'>" . Sink::find($sink)->name . "</td> " .
                    "<td align='center'>" . Sink::find($sink)->material . "</td><td></td><td></td></tr>";
            }
        }
    }
}
// Customer supplied appliances and addons
$appids = !empty($meta['quote_appliances']) ? $meta['quote_appliances'] : [];
$appliances = null;
if (empty($appids))
{
    $appids = [];
}
foreach ($appids AS $app)
{
    $appliances .= Appliance::find($app)->name . "<br/>";
}
$addons = null;
if (!empty($quote->addons))
{

    foreach ($quote->addons as $addon)
    {
        $addons .= @sprintf($addon->addon->contract, $addon->qty, $addon->description) . " <br/>";
    }
}


$data .= "<tr bgcolor='#5f9ea0' style='color: #fff'>
    <td colspan='2' align='center'><b>Customer Supplied Appliances</b></td>
    <td colspan='2' align='center'><b>Addons</b></td>
</tr>
<tr>
    <td colspan='2' align='center'>$appliances</td>
    <td colspan='2' align='center'>$addons</td>
</tr>
";


$hardware = null;

$hardware .= "
 <table valign='top' width='100%' cellspacing='0'>
 <tr bgcolor='#5f9ea0' style='color: #fff'>
    <td align='center'><b>Hardware</b></td>
    <td align='center'><b>Style</b></td>
</tr>";


if (isset($meta['quote_knobs']))
{
    foreach ($meta['quote_knobs'] AS $key => $val)
    {
        if (preg_match('/\:/', $val))
        {
            $x = explode(":", $val);
            $qty = $x[0];
            $location = " (" . $x[1] . ")";
        }
        else
        {
            $location = null;
            $qty = $val;
        }
        $hardware .= "<tr>";
        $hardware .= "<td align='center'>$qty x " . Hardware::find($key)->sku . "{$location}</td><td>Knob</td>";
        $hardware .= "</tr>";
    }
}

if (isset($meta['quote_pulls']))
{
    foreach ($meta['quote_pulls'] AS $key => $val)
    {
        if (preg_match('/\:/', $val))
        {
            $x = explode(":", $val);
            $qty = $x[0];
            $location = " (" . $x[1] . ")";
        }
        else
        {
            $location = null;
            $qty = $val;
        }
        $hardware .= "<tr>";
        $hardware .= "<td align='center'>$qty x " . Hardware::find($key)->sku . " $location</td><td>Pull</td>";
        $hardware .= "</tr>";
    }
}
$hardware .= "</table>";

$accessories = "
 <table valign='top' width='100%' cellspacing='0'>
 <tr bgcolor='#5f9ea0' style='color: #fff'>
    <td align='center'><b>Accessory</b></td>
    <td align='center'><b>SKU</b></td>
   </tr>
";
$accids = isset($meta['quote_accessories']) ? $meta['quote_accessories'] : [];
foreach ($accids AS $acc => $qty)
{

    $accessories .= "<tr>";
    $accessories .= "<td align='center'>$qty x " . Accessory::find($acc)->name . "</td><td>" .
        Accessory::find($acc)->sku . "</td> ";
    $accessories .= "</tr>";
}
$accessories .= "</table>";


$data .= "<tr><td valign='top' colspan='2'>$hardware</td><td valign='top' colspan='2'>$accessories</td></tr>";


$data .= "<tr bgcolor='#5f9ea0' style='color: #fff'>
    <td align='center'><b>Additional Items</b></td>
    <td align='center'><b></b></td>
    <td align='center'><b></b></td>
    <td align='center'><b></b></td>
</tr>";

$questions = Question::whereContract(1)->get();
foreach ($questions AS $question)
{
    foreach ($quote->answers AS $answer)
    {
        if (!$answer->question || !$answer->question->active) continue;
        if ($answer->question_id == $question->id && ($answer->answer > 0 || $answer->answer == 'on' || $answer->answer == 'Y'))
        {
            $data .= "<tr><td colspan='4'>" . sprintf($question->contract_format, $answer->answer) . "</td></tr>";
        }
    }
}

$installer_items = isset($meta['quote_installer_extras']) ?
    explode("\n", $meta['quote_installer_extras']) : [];
$indata = null;
foreach ($installer_items as $inst)
{
    $x = explode("-", $inst);
    $data .= "<tr><td colspan='4'>$x[0]</td></tr>";
}
if (isset($meta['quote_led_feet']) && $meta['quote_led_feet'] > 0)
{
    $data .= "<tr><td colspan='4'>Install $meta[quote_led_feet] linear feet of LED under cabinet lighting</td></tr>";
}
if ($quote->tiles()->count() > 0)
{
    foreach ($quote->tiles as $tile)
    {
        $in = $tile->linear_feet_counter  * 12;
        $calc1 = ($in * $tile->backsplash_height) / 144;
        $pattern = $tile->pattern;
        $will = $tile->sealed == 'Yes' ? "will" : "will not";
        $data .= "<tr><td colspan='4'>Install $calc1 sq. feet of customer supplied tile and grout. Tile will be installed in a $pattern design 
and tile {$will} be sealed.<br/></td></tr>";
    }
}


$data .= "</table>";

echo $data;
