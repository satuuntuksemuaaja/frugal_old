<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 10/1/15
 * Time: 8:15 AM
 */
use vl\quotes\QuoteGenerator;

$q = new QuoteGenerator(new Quote);
$common = $q->getSetting('commonSense');
$data = "<div class='page'><center><img src='".public_path()."/logo.png'/></center>
<h3>Additional Items</h3>

".nl2br($common)."
<br/><br/>

I, <b>{$quote->lead->customer->name}</b>, have read the entire contract and understand everything that is included. 
The Frugal Representative has explained, in detail, what is included in the project. I understand that Frugal Kitchens is 
not responsible for items that are not listed within this contract and there will be an additional charge to complete requested items
not listed within. I understand that cabinets will take 4-6 weeks to receive and unless noted on page 2, the time frames will not be altered.
 I also understand if I am picking slabs for my counters, I must send the selection sheet to either counters@frugalkitchens.com or 
 fax at 770.651.9583. Failure to do so immediately may result in the slabs requested being unavailable from the distributor.
 <br/><br/>
<table border='1' width='100%'>
<tr>
<td>
<br/>
<br/>
________________________________________<br/>
Frugal Kitchens Representative Signature
<br/><br/>
 _____________________________<br/>
 Print Name

</td>
<td>
<br/><br/>
________________________________________<br/>
BUYER/OWNER Signature
<br/><br/>
 _____________________________<br/>
 Print Name


</td>
</tr>

</table></div>";
echo $data;