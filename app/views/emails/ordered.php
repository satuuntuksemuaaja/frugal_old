<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/14/15
 * Time: 3:19 PM
 */
$contact = $customer->contacts()->first();
$content = "
<p>
$contact->name,
</p>

<p>
Your Frugal's Final Touch replacement parts has been placed by Frugal Kitchens to our suppliers.
As soon as all items have arrived in our warehouse, a Frugal representative will contact you immediately to schedule completion of your project.
</p>
<p>
We understand the urgency in completing your project and are in constant contact with our suppliers in order to obtain updates on the whereabouts of each item.
Thank you for choosing Frugal Kitchens and Cabinets, and thank you, in advance, for your patience!
</p>
<p>
Regards, <br/><br/>
Frugal Kitchens and Cabinets<br/>
770.460.4331
</p>
";
echo $content;