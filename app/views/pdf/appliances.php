<?php
$meta = unserialize($quote->meta)['meta'];
$data = "<div class='page'>
<center><img src='".public_path()."/logo.png'/></center>";

$data .= "<br/><br/><b>Appliances must be located on the same floor as the kitchen when the appliance installers arrive.</b><br/><br/>
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
    Customer Initials Required [_____]<br/><br/>";

$data .= "</div>";
echo $data;