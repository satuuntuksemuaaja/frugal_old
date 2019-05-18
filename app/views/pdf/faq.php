<?php

$data = "<div class='page'><center><img src='/web/frugalk/frugalk.com/public/logo.png'/></center>";
$data .= "<h3>Frequently Asked Questions (FAQ)</h3>";

foreach (Faq::whereType($quote->type)->whereActive(true)->orderBy('figure')->get() as $faq)
{
    $data .= "<h4><b>$faq->question</b> (See Figure $faq->figure)</h4>
        <p>".nl2br($faq->answer)."</p>
";
}



$data .= "</div>";

$data .= "<div class='page'><center><img src='/web/frugalk/frugalk.com/public/logo.png'/></center>";
$data .= "<h3>FAQ Illustrations</h3>";


foreach (Faq::whereType($quote->type)->whereActive(true)->orderBy('figure')->get() as $faq)
{
    $data .= "<div style='width: 200px; padding:20px; display: inline-block'>
            <h5>Figure $faq->figure</h5>
            <img width='200' src='".public_path()."/faq/$faq->image'>
    </div>";
}

$data .= "</div>";
echo $data;