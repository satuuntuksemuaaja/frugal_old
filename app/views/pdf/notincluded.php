<?php
$meta = unserialize($quote->meta)['meta'];
//  Countertop price will be adjusted after a template is completed by countertop contractor at $".number_format($details->granite_ppsqft,2)."/square foot.

$data = "<div class='page'><center><img src='".public_path()."/logo.png'/></center>";
$data .= "<h2>NOT INCLUDED IN THE AGREEMENT:</h2>

    The following items are specifically <b>excluded</b> from this Agreement and are provided by the BUYER/OWNER unless
    otherwise noted on page 1 or 2:<br/><br/>
<table width='100%'>
<tr>
<td>* Sheetrock work</td><td>* Painting</td><td>* Appliances</td>
</tr>
<tr>
<td>* Shoe molding</td><td>* Venting of hood fan</td><td>* Relocation of appliances</td>
</tr>
<tr>
<td>* Floor repair</td><td>* Tile work</td><td>* Faucet</td>
</tr>
<tr>
<td>* Appliance Boxes</td><td colspan='2'>* Construction Debris from other contractors or homeowners</td>
</tr>

</table>
<br/><br/><Br/>

Customer Initials _______<br/><br/>
";
if (isset($details->granite_ppsqft2))
    $addGranite = "Additional Granite price will be finalized after final template is complete at $" . number_format($details->granite_ppsqft2,2). " p/sq.ft<br/>";
    else $addGranite = null;

    $data .= "
    <ul style='list-style-type: decimal'>
    <li>If cabinet hardware (knobs/handles) is supplied by the buyer it will be installed only on the day the installer is on premise
    to install the cabinets. If hardware is supplied later there will be an additional charge for the installation.
    </li>";
if ($addGranite)
    $data .= "<li>$addGranite</li>";
$data .= "

    <li>
        Price does not include removal of tiled counter tops, granite, or Corian (unless noted on page 1 or 2).
    </li>
<li>
    If customer has existing granite counter top and is planning to save the granite for reinstallation Frugal Kitchens will not be responsible if the granite breaks during removal or reinstallation.
</li>
<li>
    Tile backsplash removal is not included in this price, (unless otherwise noted on page 1 or 2).
</li>
<li>
Shoe molding is not included in this contract and can be installed at customers request during Frugals Final Touch.
</li>
</ul>

<br/><br/><b>Appliances must be located on the same floor as the kitchen when the appliance installers arrive.</b><br/><br/>
      Installers will not move appliances from upper/lower floors or through doorways small than 40\" to the kitchen area.
      Our plumber/electrician will be at your property for one day during this installation, all appliances must be installed on that day or additional fees may apply.
      <br/><br/>
      Installers will not move appliances from upper/lower floors or through doorways smaller than 40\" wide. If appliances are left in the garage Frugal Kitchens will not move into kitchen if appliance weighs more than 100 lbs.
  <br/><br/>
    Frugal Kitchens will connect all appliances to existing connections. If the existing connections are not adequate for the new
    appliance(s), additional charges will be required to complete the install. If installing new appliances, please make sure you
    purchase connection kits for the appliances. If you do not have connection kits, you may have Frugal Kitchens supply them
    and we will add them to the final invoice unless noted on page 3.
    <br/><br/><br/>
</div>";
echo $data;