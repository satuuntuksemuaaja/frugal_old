<?php
use vl\quotes\QuoteGenerator;

$details = QuoteGenerator::getQuoteObject($quote);
$header = View::make('pdf.header')->withQuote($quote)->render();
$content = null;
if ($quote->type == 'Full Kitchen' || $quote->type == 'Cabinet Small Job')
{
    $content .= View::make('pdf.full.description')->withQuote($quote)->render(); //1
    $content .= View::make('pdf.notations')->withQuote($quote)->render(); //1
    $content .= View::make('pdf.granite')->withQuote($quote)->render(); //1
    $content .= View::make('pdf.timeprice')->withQuote($quote)->withDetails($details)->render(); //6
    $content .= View::make('pdf.notincluded')->withQuote($quote)->withDetails($details)->render(); //5
    $content .= View::make('pdf.fft')->withQuote($quote)->withDetails($details)->render(); //5
    $content .= View::make('pdf.workingkitchen')->withQuote($quote)->render(); // 8 -- attachment a 1
    if ($quote->type != 'Cabinet Small Job')
    $content .= View::make('pdf.attachmentc')->withQuote($quote)->withDetails($details)->render(); //7 - actually attachmenta

    $content .= View::make('pdf.attachmentb')->withQuote($quote)->withDetails($details)->render(); // 11 - attachment D
    if ($quote->final)
    {
        $content .= View::make('pdf.cabinetlist')->withQuote($quote)->withDetails($details)->render(); // 13 - Cabinet list and attachment F
    }

    $content .= View::make('pdf.granite_additional')->render();
    $content .= View::make('pdf.additional')->withQuote($quote)->withDetails($details)->render();


}

if ($quote->type == 'Granite Only')
{
    $content .= View::make('pdf.granite')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.timeprice')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.notations')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.attachmentd')->withQuote($quote)->withDetails($details)->render();
}

if ($quote->type == 'Cabinet Only')
{
    $content .= View::make('pdf.cabinetonly.description')->withQuote($quote)->render();
    $content .= View::make('pdf.timeprice')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.terms')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.notations')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.attachmenta')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.attachmentb')->withQuote($quote)->withDetails($details)->render();
    if ($quote->final)
    {
        $content .= View::make('pdf.cabinetlist')->withQuote($quote)->withDetails($details)->render();
    }
}
if ($quote->type == 'Builder')
{
    $content .= View::make('pdf.builder.description')->withQuote($quote)->render();
    $content .= View::make('pdf.notations')->withQuote($quote)->withDetails($details)->render();  //3
    $content .= View::make('pdf.timeprice')->withQuote($quote)->withDetails($details)->render();


}

/*if ($quote->type == 'Cabinet Small Job')
{
    $content .= View::make('pdf.cabinetonly.description')->withQuote($quote)->render();
    $content .= View::make('pdf.granite')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.appliances')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.timeprice')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.terms')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.notations')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.attachmenta')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.attachmentb')->withQuote($quote)->withDetails($details)->render();
    if ($quote->final)
    {
        $content .= View::make('pdf.cabinetlist')->withQuote($quote)->withDetails($details)->render();
    }

}
*/
if ($quote->type == 'Cabinet and Install')
{
    $content .= View::make('pdf.full.description')->withQuote($quote)->render();
    $content .= View::make('pdf.attachmente')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.notations')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.timeprice')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.attachmenta')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.attachmentb')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.attachmentc')->withQuote($quote)->withDetails($details)->render();
    $content .= View::make('pdf.fft')->withQuote($quote)->withDetails($details)->render();
    if ($quote->final)
    {
        $content .= View::make('pdf.cabinetlist')->withQuote($quote)->withDetails($details)->render();
    }
}

// Now if there are any faq pages then we can use them.
if ($quote->final)
{
    if (Faq::whereType($quote->type)->whereActive(true)->count() > 0)
    {
        $content .= View::make('pdf.faq')->withQuote($quote)->withDetails($details)->render();
    }
}


$data = "<html><body>" . $header;
$data .= $content;
$data .= "</html></body>";
echo $data;