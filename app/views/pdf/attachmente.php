<?php
$meta = unserialize($quote->meta)['meta'];


$data = "<div class='page'><center><img src='".public_path()."/logo.png'/></center>";
$data .= "
<img src='".public_path()."/images/appimages/viewer.png'/>
<hr/>
<h4>Hardware Review:</h4>
I have reviewed the hardware placement on this sheet and agree to these locations. In addition, I understand there
could be additional costs if changes are made. <b>Note:</b> Frugal Kitchens orders hardware upon signing of this agreement.
If changes are made that affects the hardware ordered, a 25% restocking fee
will be charged to the customer.
<br/><br/>";

$data .= "</div>";

echo $data;