<?php
use Carbon\Carbon;

echo BS::title("Frugal Reports");
$masterCount = 0;
$masterSold = 0;
$masterProvided = 0;

// Left side button s- right side date ranges.
$buttons = [];
$buttons[] = ['color' => 'primary', 'text' => 'Lead Sources', 'url' => "/reports", 'icon' => 'question'];
$buttons[] = ['color' => 'warning', 'text' => 'Cabinets', 'url' => "/report/cabinets", 'icon' => 'question'];
$buttons[] = ['color' => 'info', 'text' => 'Designers', 'url' => "/report/designers", 'icon' => 'question'];
$buttons[] = ['color' => 'info', 'text' => 'Download Leads Report', 'url' => "/report/all/leads", 'icon' => 'question'];
$buttons[] = ['color' => 'info', 'text' => 'Download Zip Report', 'url' => "/report/all/zips", 'icon' => 'question'];
if (Auth::user()->id == 1 || Auth::user()->id == 5)
    $buttons[] = ['color' => 'info', 'text' => 'Frugal Report', 'url' => "/report/frugal", 'icon' => 'money'];



$left = BS::buttons($buttons);

$fields = [];

$opts = [];
$opts = [
    'All Job Types'       => 'All Job Types',
    'Full Kitchen'        => 'Full Kitchen',
    'Cabinet Small Job'   => 'Cabinet Small Job',
    'Cabinet Only'        => 'Cabinet Only',
    'Cabinet and Install' => 'Cabinet and Install'
];
if (Session::get('rtype'))
{
    array_unshift($opts, Session::get('rtype'));
}
$fields[] = [
    'type' => 'input',
    'var'  => 'start',
    'text' => 'Start:',
    'val'  => Session::get('start'),
    'mask' => '99/99/99'
];
$fields[] = ['type' => 'input', 'var' => 'end', 'text' => 'End:', 'val' => Session::get('end'), 'mask' => '99/99/99'];
$fields[] = ['type' => 'select', 'var' => 'rtype', 'text' => 'Limit To:', 'opts' => $opts];

$fields[] = ['type' => 'hidden', 'var' => 'redirect', 'val' => Request::url()];
$form = Forms::init()->id('rangeForm')->url("/reports")->elements($fields)->render();
$set = Button::init()->text("Set Date Range for Reports")->color('primary post')->formid('rangeForm')->icon('save')
             ->render();
$right = $form . $set;
$type = Session::get('rtype') ?: "All Job Types";

$left = BS::span(7, $left);
$right = BS::span(5, $right);

echo BS::row($left . $right);
$headers = ['Lead Type', 'Count', 'Sold', 'Provided', 'Sold From Provided'];
$sources = LeadSource::all();
foreach ($sources AS $source)
{
    $provided = 0;
    $sold = 0;
    $count = 0;
    foreach ($source->leads AS $lead)
    {
        if (Session::has('start'))
        {
            if ($lead->created_at < Carbon::parse(Session::get('start')))
            {
                continue;
            }
        }
        if (Session::has('end'))
        {
            if ($lead->created_at > Carbon::parse(Session::get('end')))
            {
                continue;
            }

        }
        $count++;
        $masterCount++;
        if ($lead->status_id == 10 || $lead->provided)
        {
            $provided++;
            $masterProvided++;
        }
        foreach ($lead->quotes as $quote)
        {
            if ($type != 'All Job Types')
            {
                if ($quote->type == $type && $quote->accepted)
                {
                    $sold++;
                    $masterSold++;
                }
            }
            else
            {
                if ($quote->accepted)
                {
                    $sold++;
                    $masterSold++;
                }
            }

        }


    } // fe lead
    if ($count > 0)
    {
        $providedPerc = "<a class='mjax' data-target='#workModal' href='/report/sources/$source->id/provided'>$provided</a> <span class='pull-right'>(" . round($provided / $count * 100) . "%)</span>";
        $soldPerc = "<a class='mjax' data-target='#workModal' href='/report/sources/$source->id/sold'>$sold</a><span class='pull-right'> (" . round($sold / $count * 100) . "%)</span>";
    }
    else
    {
        $providedPerc = 0;
        $soldPerc = 0;
    }
    $rows[] = [
        $source->type,
        "<a class='mjax' data-target='#workModal' href='/report/sources/$source->id/count'>$count</a>",
        $soldPerc,
        $providedPerc,
        @number_format($sold / $provided * 100) . "%"
    ];
} // fe source
$rows[] = [
        null,
        number_format($masterCount),
        number_Format($masterSold),
        number_format($masterProvided),
    @number_format($masterSold / $masterProvided * 100) . "%"
];

$table = Table::init()->headers($headers)->rows($rows)->render();
$headers = ['User', 'Count', 'Sold', 'Provided'];
$rows = [];
// Do similar for Designers.
$type = Session::get('rtype') ?: "All Job Types";
foreach (User::whereActive(true)->get() AS $user)
{
    $provided = 0;
    $sold = 0;
    $count = 0;
    foreach ($user->quotes AS $quote)
    {
        if (Session::has('start'))
        {
            if ($quote->created_at < Carbon::parse(Session::get('start')))
            {
                continue;
            }
        }
        if (Session::has('end'))
        {
            if ($quote->created_at > Carbon::parse(Session::get('end')))
            {
                continue;
            }

        }
        $count++;
        $masterCount++;
        if ($quote->lead->status_id == 10 || $quote->lead->provided)
        {
            $provided++;
            $masterProvided++;
        }

        if ($type != 'All Job Types')
        {
            if ($quote->type == $type && $quote->accepted)
            {
                $sold++;
                $masterSold++;
            }
        }
        else
        {
            if ($quote->accepted)
            {
                $sold++;
                $masterSold++;
            }
        }

    } // fe lead
    if ($count > 0)
    {
        $providedPerc = $provided . " <span class='pull-right'>(" . round($provided / $count * 100) . "%)</span>";
        $soldPerc = $sold . "<span class='pull-right'> (" . round($sold / $count * 100) . "%)</span>";
    }
    else
    {
        $providedPerc = 0;
        $soldPerc = 0;
    }
    $rows[] = [
        $user->name,
        "<a class='mjax' data-target='#workModal' href='/report/user/$user->id/count'>$count</a></span>",
        "<a class='mjax' data-target='#workModal' href='/report/user/$user->id/sold'>$sold</a>  <span class='pull-right'>(" . @round($sold / $count * 100) . "%)</span>",
        "<a class='mjax' data-target='#workModal' href='/report/user/$user->id/provided'>$provided</a>  <span class='pull-right'>(" . @round($provided / $count * 100) . "%)</span>",

    ];
} // fe source
$table .= Table::init()->headers($headers)->rows($rows)->render();

echo BS::span(6, $table);


echo Modal::init()->id('workModal')->onlyConstruct()->render();
