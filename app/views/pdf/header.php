<?php
//<img src='".asset('logo.png')."' alt='' /> ";
use vl\core\Formatter;

$contact = $quote->lead->customer->contacts()->first();
$number = Formatter::numberFormat($contact->home);
$mobile = Formatter::numberFormat($contact->mobile);
if (!$quote->final)
    $water ="<div id='watermark'>INITIAL</div>";
else $water = null;

$address = $quote->lead->customer->job_address
    ? $quote->lead->customer->job_address . "<br/>" . $quote->lead->customer->job_city . ", " . $quote->lead->customer->job_state . " " . $quote->lead->customer->job_zip
    : $quote->lead->customer->address . "<br/>" . $quote->lead->customer->city . ", " . $quote->lead->customer->state . " " . $quote->lead->customer->zip;

$data = "<style>
body {
        font-family: 'Verdana';
        font-size:14px;
}
.page {
        page-break-before: always;
        font-family: 'Verdana';
        font-size:14px;
      }
.footer { 
            position: fixed;
            bottom: 0px;
}
 #watermark {
    position: fixed;
    top: 45%;
    width: 100%;
    text-align: center;
    font-size: 160px;
    opacity: .08;
    transform: rotate(30deg);
    transform-origin: 50% 50%;
    z-index: -1000;
  }
      .pagenum:before { content: counter(page); }
</style>
{$water}
<center><img src='".public_path()."/logo.png'/>


<h2 style='border:2px solid #000000'>THIS AGREEMENT IS BETWEEN</h2>

<table width='100%' cellpadding='3' style='font-size:10px;'>
<tr>
  <td>
    Frugal Kitchens & Cabinets<br/>
    625 W. Crossville Rd<br/>
    Suite 126<br/>
    Roswell, GA 30075<br/>
    770.460.4331
  </td>
  <td>
    Frugal Kitchens & Cabinets<br/>
    180 N 85th Parkway<br/>
    Suite A<br/>
    Fayetteville, GA 30214<br/>
    770.460.4331
  </td>
  <td>
    Frugal Kitchens & Cabinets<br/>
    2855 N. Druid Hills<br/>
    Suite B<br/>
    Atlanta, GA 30329<br/>
    770.460.4331
  </td>
  <td>
  Frugal Kitchens & Cabinets<br/>
  3732 Cedarcrest Road<br/>
  Suite A102<br/>
  Acworth, GA 30101<br/>
  770.460.4331
  </td>
  <td>
  Frugal Kitchens & Cabinets<br/>
  361 Hwy 74 N<br/>
  <br/>
  Peachtree City, GA 30269<br/>
  770.460.4331
  </td>
  <tr/>
</table>

<h4 style='border:2px solid #000000'>AND CLIENT:</h4>
</center>
<table border=0 width=\"100%\">
<tr>
<td>
$contact->name <br/>
{$address}
<br/>

</td>
<td align='right'>
Phone: {$number} <br/>
Mobile: {$mobile} <br/>
E-mail Address: {$contact->email}

</td>
</tr>
</table>

<div class='footer'>
Customer Signature ______________________________ <br/>{$quote->lead->customer->name}, " . \Carbon\Carbon::now()
        ->toDayDateTimeString() . "

&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
Designer Initials [_____]  &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp; Page: <span class='pagenum'></span></div>
";
echo $data;

