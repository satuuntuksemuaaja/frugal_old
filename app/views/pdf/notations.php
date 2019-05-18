<?php
function renderImage($title, $image)
{
    return "<div style='display: inline-block; padding:20px; width:200px;'>
        <h4>$title</h4>
        <img width='200' src='" . public_path() . $image . "'/>
        </div>";
}

$meta = unserialize($quote->meta)['meta'];
$data = "<div class='page'><center><img src='" . public_path() . "/logo.png'/></center>";
if (!empty($meta['quote_special']))
{
    $data .= "<h3>The following items have been added/notated for this contract:</h3>";
    $special = (isset($meta['quote_special']) && $meta['quote_special']) ? $meta['quote_special'] : null;
    $data .= nl2br($special);
}
if ($quote->responsibilities()->count() > 0)
{
    $data .= "<br/><Br/>";
    $data .= "<h3>Customer understands that they are responsible for the following items:</h3>";
    foreach ($quote->responsibilities as $r)
    {
        $data .= "<li>" . $r->responsibility->name . "</li>";
    }
}
$data .= "<br/><br/><div style='width:95%'>";


foreach ($quote->cabinets as $cabinet)
{
    if ($cabinet->cabinet->image)
    {
        $data .= renderImage("Cabinet $cabinet->id ({$cabinet->cabinet->name})",
            "/cabinet_images/" . $cabinet->cabinet->image);
    }
}

if (!empty($meta['quote_knobs']))
{
    foreach ($meta['quote_knobs'] as $k => $val)
    {
        $hw = Hardware::find($k);
        if (!empty($hw->image))
        {
            $data .= renderImage($hw->sku, "/hardware_images/" . $hw->image);
        }
    }
}

if (!empty($meta['quote_pulls']))
{
    foreach ($meta['quote_pulls'] as $k => $val)
    {
        $hw = Hardware::find($k);
        if (!empty($hw->image))
        {
            $data .= renderImage($hw->sku, "/hardware_images/" . $hw->image);
        }
    }
}
if (!empty($meta['quote_accessories']))
{
    foreach ($meta['quote_accessories'] AS $k => $val)
    {
        $acc = Accessory::find($k);
        if (!empty($acc->image))
        {
            $data .= renderImage($acc->sku, "/acc_images/" . $acc->image);
        }
    }
}

foreach ($quote->granites as $granite)
{
    switch ($granite->counter_edge)
    {
        case '(Premium) 2cm Ogee ($14/lnft.)' :
            $img = "2cmo.png";
            break;
        case '(Premium) Demi Bullnose ($5/lnft.)' :
            $img = "demi-bullnose.jpg";
            break;
        case '(Premium) Dupont ($24/lnft.)' :
            $img = "d.png";
            break;
        case '(Premium) French Ogee ($20/lnft.)' :
            $img = 'fo.png';
            break;
        case '(Premium) Full Bull Nose ($12/lnft.)' :
            $img = 'fbn.png';
            break;
        case '(Premium) Half Bevel ($8/lnft.)' :
            $img = '12bevel.png';
            break;
        case '(Premium) Half Bull Nose ($8/lnft.)' :
            $img = 'hbn.png';
            break;
        case '(Standard) 1/4 Bevel' :
            $img = '14bevel.png';
            break;
        case '(Standard) Eased' :
            $img = 'eased.png';
            break;
        case '(Standard) Pencil Round - 1/4 Round';
            $img = 'pr14r.png';
            break;
    }
    $data .= renderImage($granite->counter_edge, "/images/appimages/$img");
}


$data .= "</div></div>";
echo $data;

