<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 7/15/15
 * Time: 11:06 AM
 */

$quote = $job->quote;
$meta = unserialize($quote->meta)['meta'];
$accessoryData = null;
$hardwareData = null;
// Accessory.
$accids = (isset($meta['quote_accessories'])) ? $meta['quote_accessories'] : [];
foreach ($accids AS $acc => $qty)
{

    $accessory = Accessory::find($acc);
    $accessoryData .= "<tr><td>{$accessory->sku} - {$accessory->name}</td><td>$qty</td></tr>";
}

$accids = (isset($meta['quote_pulls'])) ? $meta['quote_pulls'] : [];
foreach ($accids AS $acc => $qty)
{
    $hardware = Hardware::find($acc);
    $hardwareData .= "<tr><td>(PULL) {$hardware->sku} - $hardware->description</td><td>$qty</td></tr>";
}

$accids = (isset($meta['quote_knobs'])) ? $meta['quote_knobs'] : [];
foreach ($accids AS $acc => $qty)
{
    $hardware = Hardware::find($acc);
    $hardwareData .= "<tr><td>(KNOB) {$hardware->sku} - $hardware->description</td><td>$qty</td></tr>";
}


$cabinets = null;
foreach ($quote->cabinets AS $cabinet)
{
    $cabinets .= $cabinet->cabinet->frugal_name;
    if ($cabinet->color)
        $cabinets .= " ({$cabinet->color})";
    $cabinets .= "<br/>";
}

$check = null;
$cat = null;
foreach (Checklist::orderBy('category')->get() AS $checklist)
{
    if ($checklist->category != $cat)
    {
        if ($cat)
            $check .= "</table><br/><br/><b>Signature: ________________________________________________________</b>";
        $check .= "<h4 style='background-color: yellow; border: 2px solid #000000; padding: 10px; text-align: center;'>$checklist->category</h4>";
        $cat = $checklist->category;
        $check .= "<table border=1 cellpadding='4' width='100%'>";
    }

    $check .= "<tr><td width='95%'>$checklist->question</td><td><input type='text' width='20px'></td></tr>";
}
$check .= "</table><br/><br/><b>Signature: ________________________________________________________</b>";


$special = (isset($meta['quote_special']) && $meta['quote_special']) ? "<h4>Special Instructions:</h4><p>" .$meta['quote_special'] . "</p>": null;

// Cabinet List
$cabList = null;
foreach ($quote->cabinets AS $cabinet)
{
    if (!isset($cabinet->cabinet->frugal_name))
    {
        $cabList .= "Unassigned Cabinet Name";
    }
    else
    {
        $cabList .= "<h4>{$cabinet->cabinet->frugal_name}</h4>
  <p style='font-size:12px;'>";
    }
    if ($cabinet->override)
    {
        $cabData = unserialize($cabinet->override);
    }
    else
    {
        $cabData = unserialize($cabinet->data);
    }
    $instItems = 0;
    $cabItems = 0;
    $attCount = 0;
    foreach ($cabData AS $item)
    {
        if (!isset($item['attachment']))
        {
            if (!isset($item['description']))
            {
                $item['description'] = null;
            }

            $cabList .= "($item[sku]) - $item[description] x " . $item['qty'] . " - $item[price]<br/>";
            $cabItems += $item['qty'];
            $instItems += $item['qty']; // Installer items.
        }
        else
        {
            if (!isset($item['description']))
            {
                $item['description'] = null;
            }

            $cabList .= "Attachment: ($item[sku]) - $item[description] x " . $item['qty'] . "- $item[price]<br/> ";
            $attCount += $item['qty'];
            $cabItems += $item['qty'];
        }
    }
    $cabList = nl2br($cabList) . "</p>";
    if ($cabinet->wood_xml)
    {
        $cabList .= "<h5>Additional Wood Products added to Order</h5>";
        foreach (QuoteGenerator::returnWoodArray($cabinet) as $wood)
        {
            $cabList .= "($wood[sku]) - $wood[description] x " . $wood['qty'] . " - $wood[price]<br/>";

        }
    }

}

$questionData = "<table border=1 cellpadding=1 width='100%'>
<tr>
<td><b>Question</b></td><td><b>Answer</b></td>
</tr>";
foreach ($quote->answers AS $answer)
{
    if (!$answer->question)
    {
        continue;
    }

    if (!$answer->question->active)
    {
        continue;
    }

    if ($answer->answer == 'on')
    {
        $answer->answer = 'Y';
    }

    if (!$answer->question->on_checklist)
    {
        continue;
    }
    $questionData .= "<tr><td>{$answer->question->question}</td><td>{$answer->answer}</td></tr>";
}
$questionData .= "</table><br/><br/>";

$data = "
<div style='font-size: 12px;'>
<h4 style='background-color: yellow; border: 2px solid #000000; padding: 10px; text-align: center;'>Customer</h4>
<div style='text-align:center'>

{$job->quote->lead->customer->contacts()->first()->name} <br/>
{$job->quote->lead->customer->address} <br/>
{$job->quote->lead->customer->city}, {$job->quote->lead->customer->state} {$job->quote->lead->customer->zip}<br/>
M: {$job->quote->lead->customer->contacts()->first()->mobile} / H: {$job->quote->lead->customer->contacts()->first()->home} / A: {$job->quote->lead->customer->contacts()->first()->alternate} <br/>

<br/>
<b>Cabinet(s): $cabinets</b>
<br/>
</div>
$check

<br/><br/>

<table border='1' cellpadding='4' width='100%'>
<tr>
    <td><b>Accessory</b></td><td><b>QTY</b></td>
</tr>
{$accessoryData}
</table>
<br/>
<table border='1' cellpadding='4' width='100%'>
<tr>
    <td><b>Hardware</b></td><td><b>QTY</b></td>
</tr>
{$hardwareData}
</table>

{$special}

{$questionData}
{$cabList}

</div>
";


echo $data;