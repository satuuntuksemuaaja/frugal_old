<?php
echo BS::title("Frugal Profit Report");
use Carbon\Carbon;
use vl\quotes\QuoteGenerator;

if (!Session::has('start'))
{
    $year = date("y", time());
    Session::put('start', "01/01/$year");
    Session::put('end', "12/31/$year");
    Session::save();
}
// Left side button s- right side date ranges.
$buttons = [];
$buttons[] = ['color' => 'primary', 'text' => 'Lead Sources', 'url' => "/reports", 'icon' => 'question'];
$buttons[] = ['color' => 'warning', 'text' => 'Cabinets', 'url' => "/report/cabinets", 'icon' => 'question'];
$buttons[] = ['color' => 'info', 'text' => 'Designers', 'url' => "/report/designers", 'icon' => 'question'];
if (Auth::user()->id == 1 || Auth::user()->id == 5)
    $buttons[] = ['color' => 'info', 'text' => 'Frugal Report', 'url' => "/report/frugal", 'icon' => 'money'];

$left = BS::buttons($buttons);

$fields = [];
$fields[] = [
    'type' => 'input',
    'var'  => 'start',
    'text' => 'Start:',
    'val'  => Session::get('start'),
    'mask' => '99/99/99'
];
$opts = [];
$opts = [
    'All Job Types' => 'All Job Types',
    'Full Kitchen' => 'Full Kitchen',
    'Cabinet Small Job' => 'Cabinet Small Job',
    'Cabinet Only' => 'Cabinet Only',
    'Cabinet and Install' => 'Cabinet and Install'
];
if (Session::get('rtype'))
    array_unshift($opts, Session::get('rtype'));

$fields[] = ['type' => 'input', 'var' => 'end', 'text' => 'End:', 'val' => Session::get('end'), 'mask' => '99/99/99'];
$fields[] = ['type' => 'select', 'var' => 'rtype', 'text' => 'Limit To:', 'opts' => $opts];
$fields[] = ['type' => 'hidden', 'var' => 'redirect', 'val' => Request::url()];

$form = Forms::init()->id('rangeForm')->url("/reports")->elements($fields)->render();
$set = Button::init()->text("Set Date Range for Reports")->color('primary post')->formid('rangeForm')->icon('save')->render();
$right = $form . $set;

$left = BS::span(6, $left);
$right = BS::span(6, $right);

echo BS::row($left . $right);
$type = Session::get('rtype') ?: "All Job Types";
$grand = 0;
$allttl = 0;
$headers = ['Designer', 'Jan', 'Feb', 'Mar', "Apr", 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TTL'];
for ($i = 1; $i <= 12; $i++)
{
    $grandTTL[$i] = 0;
    $grandMonth[$i] = 0;

}
foreach (User::whereLevelId(4)->orWhere('id', '=', 5)->get() AS $user)
{
    $months = [];
    $count = [];
    $ttl = 0;
    $userttl = 0;
    $quotesList = [];
    for ($i = 1; $i <= 12; $i++)
    {
        $quotesList[$i] = null;
    }
    foreach ($user->quotes AS $quote)
    {

        if ($quote->accepted)
        {
            if (!$quote || !$quote->job) continue;
            if (Session::has('start') && $quote->job->created_at < Carbon::parse(Session::get('start'))) continue;
            if (Session::has('end') && $quote->job->created_at > Carbon::parse(Session::get('end'))) continue;
            $details = QuoteGenerator::getQuoteObject($quote);
            $financeTotal = $details->forFrugal;
            $financeTotal += 150.00;
            $financeTotal += $details->cabinetBuildup;
            $financeTotal -= $details->discounts;
            if ($type != 'All Job Types')
            {
                if ($quote->type != $type) continue;
            }

            $month = $quote->job->created_at->format('n');

            if (!isset($months[$month])) $months[$month] = 0;
            if (!isset($count[$month])) $count[$month] = 0;
            if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
            if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
            if (!isset($quotesList[$month])) $quotesList[$month] = null;
            $months[$month] = $months[$month] + $financeTotal;
            $count[$month] = $count[$month] + 1;
            $grandTTL[$month] = $grandTTL[$month] + 1;
            $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
            $quotesList[$month] .= $quote->id . ", ";
            $userttl++;
            $allttl++;
            $ttl += $financeTotal;
            $grand += $financeTotal;
        } // if accepted
    } // fe quote
    for ($i = 1; $i <= 12; $i++)
    {
        if (!isset($months[$i])) $months[$i] = 0;
        "$" . $months[$i] = number_format($months[$i], 2);
    }
    $rows[] = [
        $user->name,
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/1?profit=true'>$months[1]</a>" . " (" . @$count[1] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/2?profit=true'>$months[2]</a>" . " (" . @$count[2] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/3?profit=true'>$months[3]</a>" . " (" . @$count[3] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/4?profit=true'>$months[4]</a>" . " (" . @$count[4] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/5?profit=true'>$months[5]</a>" . " (" . @$count[5] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/6?profit=true'>$months[6]</a>" . " (" . @$count[6] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/7?profit=true'>$months[7]</a>" . " (" . @$count[7] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/8?profit=true'>$months[8]</a>" . " (" . @$count[8] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/9?profit=true'>$months[9]</a>" . " (" . @$count[9] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/10?profit=true'>$months[10]</a>" . " (" . @$count[10] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/11?profit=true'>$months[11]</a>" . " (" . @$count[11] . ")",
        "<a class='mjax' data-target='#workModal' href='/report/designers/$user->id/12?profit=true'>$months[12]</a>" . " (" . @$count[12] . ")",
        number_format($ttl, 2) . " ($userttl)"
    ];
}
$rows[] = [
    null,
    number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
    number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
    number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
    number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
    number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
    number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
    number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
    number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
    number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
    number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
    number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
    number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
    number_format($grand, 2) . " ($allttl)"

];

$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
echo BS::span(12, $table);
echo Modal::init()->id('workModal')->onlyConstruct()->render();
