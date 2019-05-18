<?php
$meta = unserialize($quote->meta);

$data = "<center><h3>DESCRIPTION OF PROJECT</h3></center>
Frugal Kitchens & Cabinets will construct the improvements described in this Agreement generally
as follows and within industry standards: ";
$list = null;
foreach ($quote->cabinets AS $cabinet)
{
    $list .= $cabinet->cabinet->frugal_name . " Cabinets ";
    if ($cabinet->color && $cabinet->color != 'nocolor') $list .= " ($cabinet->color) ";
    if ($cabinet->cabinet && $cabinet->cabinet->description) $list .= " ({$cabinet->cabinet->description}) ";
    $list .= ", ";
}
$list = substr($list, 0, -2);
$data .= "Install the following new cabinets as per list: $list";
echo $data;

if ($quote->type == 'Cabinet and Install')
{
    $data = "<br/><br/>";

// Accessories.

    $accids = isset($meta['meta']['quote_accessories']) ? $meta['meta']['quote_accessories'] : [];
    $field = null;
    foreach ($accids AS $acc => $qty)
    {
        $field .= "$qty x " . Accessory::find($acc)->name . " - (" . Accessory::find($acc)->sku . ")<br/><br/>";
    }


    $field = "<div style='border: 3px solid #dedede'> $field </div>";
    if ($accids)
    {
        $data .= "<h3>Accessories</h3>* The following accessories have been included in this quote and will be installed<br/><br/>
  ";
        $data .= $field;
    }
    else
    {
        $data .= "* No accessories have been selected to be installed.";
    }

// Hardware
//
// Pulls and Knobs.
    $knobttl = 0;
    $knames = null;
    $pnames = null;
    if (isset($meta['meta']['quote_knobs']))
    {
        foreach ($meta['meta']['quote_knobs'] AS $key => $val)
        {
            $knames .= Hardware::find($key)->sku . ", ";
            $knobttl += $val;
        }
    }

    $knames = substr($knames, 0, -2);

    $pullttl = 0;
    if (isset($meta['meta']['quote_pulls']))
    {
        foreach ($meta['meta']['quote_pulls'] AS $key => $val)
        {
            $pnames .= Hardware::find($key)->sku . ", ";
            $pullttl += $val;
        }
    }

    $pnames = substr($pnames, 0, -2);
    if ($knames || $pnames)
    {
        $data .= "<h3>Hardware</h3>
    The following cabinet hardware is to be installed:<br/><br/>
    Pulls: $pnames with a quantity of $pullttl<br/>
    Knobs: $knames with a quantity of $knobttl ";
    }
    else $data .= "<br/><br/>* No hardware has been selected to be installed.";
    echo $data;
}