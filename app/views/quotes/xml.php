<?php
use vl\quotes\QuoteGenerator;

if (!isset($cabinet->cabinet->frugal_name))
{
    $cabList = "Unassigned Cabinet Name";
}
else
{
    $cabList = "<h4>{$cabinet->cabinet->frugal_name}</h4>
  <p style='font-size:10px;'>";
}

$cabData = unserialize($cabinet->data);
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
echo Modal::init()->isInline()->header("Cabinet Item List")->content($cabList)->render();
