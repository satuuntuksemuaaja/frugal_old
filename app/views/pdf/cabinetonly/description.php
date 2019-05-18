<?php
$meta = unserialize($quote->meta);
$data = "<h3>Cabinets</h3>
<p>I have viewed the attached cabinet list. I understand that this is a special order and once I have verified and signed
this contract I am obligated to pay the contract price. Also, I will not be able to cancel or exchange any part of
the attached list.
</p>
<br/><br/>
<p>
<p>
___________________________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;            _________________________________
<br/><br/>
&nbsp;&nbsp;&nbsp;&nbsp;        BUYER/OWNER  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;      &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; DATE
</p>
<h3>Delivery</h3>
<p>
Once the cabinet order has arrived at our warehouse, a representative from Frugal Kitchens will call to schedule a delivery date (if applicable). The BUYER/OWNER must be present at time of delivery to inspect order and sign delivery/inspection receipt.
</p>
<p>
If buyer or owner does not inspect the cabinets Frugal Kitchens will not be responsible for any damages that are noticed after the install that Frugal Kitchens deems installer error.</p>
<p>
If customer elects to pick up the cabinet order, our warehouse is located at our Fayetteville location.</p>
";

$list = null;
foreach ($quote->cabinets AS $cabinet)
{
    $list .= $cabinet->cabinet->frugal_name . " Cabinets ";
    if ($cabinet->color && $cabinet->color != 'nocolor') $list .= " ($cabinet->color) ";
    if ($cabinet->cabinet && $cabinet->cabinet->description) $list .= " ({$cabinet->cabinet->description}) ";
    $list .= "<br/>";
    $data .= "<p style='border: 2px solid #dedede; text-align:center;'>Supply <b>{$cabinet->cabinet->frugal_name}</b> cabinets for customer installation.<br/>";
    if ($cabinet->delivery == 'Custom Delivery')
    {
        $data .= "<p>You have chosen a custom delivery, which means Frugal Kitchens will deliver cabinets inside a home with doorways at least 36\" wide.
                If they do not fit through doorway, they will be left in garage for the cabinet installers.</p>";
    }
    else
    {
        if ($cabinet->delivery == 'Curbside Delivery')
        {
            $data .= "<p>You have chosen curbside delivery, which means Frugal Kitchens will deliver the cabinets to a ground level garage or driveway.
                No cabinets will be brought into the home by Frugal Kitchens at that time.</p>";
        }
    }
}
$data .= "(See Attachment A for Painted Wood Cabinets and Attachment B for Stained Wood Cabinets.)";

echo $data;