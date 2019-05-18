<?php

$data = "<div class='page'><center><img src='".public_path()."/logo.png'/></center>";
$data .= "<h3>Step-by-Step Guide to your Kitchen Installation* </h3>
After signing a contract with your designer, your cabinets will take approximately 4-6 weeks for delivery (depending on manufacturer).
Once the cabinets arrive and have been inventoried, Frugal Kitchens will contact you to set-up installation date (occasionally, cabinets will be damaged by the freight company and we will need to reorder damaged cabinets. If this occurs, you will be notified immediately of the delay). You will receive an email invitations advising you of the installation dates and times.
<br/><br/>
    <b>Day One</b> - Installers are scheduled to begin at 8am (due to traffic and unknown events, there might be a slight delay with our scheduled start time). Upon arrival they will begin to remove existing cabinets (if necessary) and disconnect appliances and sink. They will then begin laying out and installing your new cabinets according to industry standards. If cabinets need to be removed on a separate day there will be an additional charge.
<br/><br/>
    <b>Day Two</b> - Cabinet installers will finish installing cabinets, hardware and trim (depending on the size of the kitchen, this process may take an additional day). Installers will then require the homeowner to inspect the cabinets. In the afternoon, the granite company will template counters. Frugal Kitchens strongly recommends Homeowner to be present during the templating process to confirm size, shape, edge, type, sink and backsplash.
<br/><br/>
";
if ($quote->type != 'Cabinet and Install')
    $data .= "
    <b>Day Three</b> - Cabinet installers will finish installing cabinets, if necessary. Granite company will fabricate countertops at shop. If cabinet installation was complete on previous day, there will not be any work performed on this day.
<br/><br/>
    <b>Day Four</b> - Granite company will install new countertops, sink and drill holes for faucet according to industry standards. If faucet is not present, there will be an additional charge to have the granite company return.
<br/><br/>
    <b>Day Five</b> - Plumber and Electrician will install customer supplied appliances and faucet, that are noted on your contract. Any additional work beyond your contract will be between the Homeowner and the contractor, this would be beyond your agreement with Frugal Kitchens and Cabinets.
    If you need any additional remodel work done, please call the office at 770-460-4331.
    <br/><br/>
    * This is the standard installation process for cabinets, granite and appliance installation. If other work is being done
    a separate schedule will be customized for your job.";

$data .= "</div>";
echo $data;
