<?php
$data = "<div class='page'>
<center><img src='" . public_path() . "/logo.png'/></center>";
$data .= "<h3>Cabinet List</h3>";
$cabList = [];
$numTables = ceil($quote->cabinets()->count() / 2);
foreach ($quote->cabinets AS $cabinet)
{
    $list = null;
    $list .= "<b>{$cabinet->cabinet->frugal_name}</b>
  <p style='font-size:14px;'>";
    $cabData = unserialize($cabinet->data);
    $instItems = 0;
    $cabItems = 0;
    $attCount = 0;
    foreach ($cabData AS $item)
    {
        if (!isset($item['attachment']))
        {
            if (!isset($item['description'])) $item['description'] = null;
            $list .= "($item[sku]) - $item[description] x " . $item['qty'] . ", ";
            $cabItems += $item['qty'];
            $instItems += $item['qty']; // Installer items.
        }
        else
        {
            if (!isset($item['description'])) $item['description'] = null;
            $list .= "Attachment: ($item[sku]) - $item[description] x " . $item['qty'] . ", ";
            $attCount += $item['qty'];
            $cabItems += $item['qty'];
        }
    }
    $cabList[] = nl2br($list);

}
$xport = null;
$on = 0;
/*
for ($i= 1; $i <= $numTables; $i++)
{
    $xport .= "<tr valign='top'>";
    if (!empty($cabList[$on]))
        $xport .= "<td>$cabList[$on]</td>";
    $on++;
    if (!empty($cabList[$on]))
        $xport .= "<td>$cabList[$on]</td>";
    $on++;
    $xport .= "</tr>";
}
*/
foreach ($cabList as $cab)
{
    $xport .= "$cab";
}

$data .= "
 The following cabinet items are included in this contract:
<br/><br/>
    <div style='font-size:16px'>$xport</div>
 </div>";
echo $data;