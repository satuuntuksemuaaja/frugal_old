<?php

use Carbon\Carbon;
use vl\quotes\QuoteGenerator;

define('SHOWROOM_SCHEDULED', 4);
define('QUOTE_PROVIDED', 10);
define('SOLD', 14);
define('WAITING_FOR_CUSTOMER', 12);
define('NO_SHOW', 49);

/**
 * Get a weekly status array for the status provided.
 *
 * @param      $status
 * @param null $user Override for Admin
 * @return
 */
function weeklyFor($status, $user = null)
{
    if (!$user)
    {
        $user = Auth::user()->id;
    }
    for ($i = 0; $i <= 6; $i++)
    {
        $start = Carbon::now()->startOfWeek()->addDays($i);
        $end = Carbon::now()->startofWeek()->addDays($i)->endOfDay();
        $count[$i] = "<a class='mjax' data-target='#workModal' href='/report/dashboard/range/$status/$user/" . $start->toDateString() . "/" . $end->toDateString() . "/'>" .
            LeadUpdate::whereStatus($status)
                ->whereUserId($user)
                //->groupBy('lead_id')
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->count() . "</a>";
    }
    return $count;
}

/**
 * Get monthly status for status provided.
 *
 * @param $status
 * @return array
 */
function monthlyFor($status, $user = null)
{
    if (!$user)
    {
        $user = Auth::user()->id;
    }
    $total = 0;
    for ($i = 0; $i <= 11; $i++) // we start with 1 already for the month
    {
        $start = Carbon::now()->startOfYear()->addMonths($i);
        $end = Carbon::now()->startOfYear()->addMonths($i)->endOfMonth();
        //   Log::info("[$i] Checking $start to $end");
        $cCount = LeadUpdate::whereStatus($status)
            ->whereUserId($user)
            //->groupBy('lead_id')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->count();
        $count[$i] = "<a class='mjax'
                data-target='#workModal'
                href='/report/dashboard/range/$status/$user/" . $start->toDateString() . "/" . $end->toDateString() . "/'>"
            . $cCount . "</a>";


        $total += $cCount;
    }
    $count[] = $total;
    return $count;
}

/**
 * Get monthly status for status provided.
 *
 * @param $status
 * @return array
 */
function yearlyFor($status, $user = null, $year)
{
    if (!$user)
    {
        $user = Auth::user()->id;
    }
    $total = 0;
    $start = Carbon::parse("{$year}-01-01");
    $end = Carbon::parse("{$year}-12-31");
    //   Log::info("[$i] Checking $start to $end");
    $cCount = LeadUpdate::whereStatus($status)
        ->whereUserId($user)
        //->groupBy('lead_id')
        ->where('created_at', '>=', $start)
        ->where('created_at', '<=', $end)
        ->count();
    $count[$year] = "<a class='mjax'
                data-target='#workModal'
                href='/report/dashboard/range/$status/$user/" . $start->toDateString() . "/" . $end->toDateString() . "/'>"
        . $cCount . "</a>";
    $total += $cCount;
    $count[] = $total;
    return $count;
}


/**
 * Show Weekly Table
 *
 * @return mixed
 */
function weekly()
{
    $headers = ['Item', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    if (!Auth::user()->superuser)
    {
        $rows = [];
        $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', weeklyFor(SHOWROOM_SCHEDULED)];
        $rows[] = ['<span class="pull-right"><b>Quote Provided', weeklyFor(QUOTE_PROVIDED)];
        $rows[] = ['<span class="pull-right"><b>Commissions in Pipeline', getPipeline('week', QUOTE_PROVIDED)];
        $rows[] = ['<span class="pull-right"><b>Sold', weeklyFor(SOLD)];
        $rows[] = ['<span class="pull-right"><b>No Shows', weeklyFor(NO_SHOW)];
        $rows[] = ['<span class="pull-right"><b>Waiting for Customer', weeklyFor(WAITING_FOR_CUSTOMER)];
        $rows[] = ['<span class="pull-right"><b>Commissions Earned', getPipeline('week', SOLD)];
    }
    else
    {
        $rows = [];
        foreach (User::whereLevelId(4)->get() AS $user)
        {
            if (!$user->active) continue;
            $rows[] = ['<b>' . $user->name . "</b>"];
            $rows[] = [];
            $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', weeklyFor(SHOWROOM_SCHEDULED, $user->id)];
            $rows[] = ['<span class="pull-right"><b>Quote Provided', weeklyFor(QUOTE_PROVIDED, $user->id)];
            $rows[] = [
                '<span class="pull-right"><b>Commissions in Pipeline',
                getPipeline('week', QUOTE_PROVIDED, $user->id)
            ];
            $rows[] = ['<span class="pull-right"><b>Sold', weeklyFor(SOLD, $user->id)];
            $rows[] = ['<span class="pull-right"><b>No Shows', weeklyFor(NO_SHOW, $user->id)];
            $rows[] = ['<span class="pull-right"><b>Waiting for Customer', weeklyFor(WAITING_FOR_CUSTOMER, $user->id)];
            $rows[] = ['<span class="pull-right"><b>Commissions Earned', getPipeline('week', SOLD, $user->id)];
        }
    }
    return Table::init()->headers($headers)->rows($rows)->render();
}

function yearly()
{
    $request = app('request');
    if (!$request->has('setYear'))
    {
        return "
        <a href='/dashboard?setYear=2018'>Show yearly for 2018<br/><br/>
        <a href='/dashboard?setYear=2017'>Show yearly for 2017<br/><br/>
        <a href='/dashboard?setYear=2016'>Show yearly for 2016<br/><br/>";
    }
    $headers = ['Item', 'Year'];
    $year = $request->get('setYear');
    if (!Auth::user()->superuser)
    {
        $rows = [];
        $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', yearlyFor(SHOWROOM_SCHEDULED, null, $year)];
        $rows[] = ['<span class="pull-right"><b>Quote Provided', yearlyFor(QUOTE_PROVIDED, null, $year)];
        $rows[] = ['<span class="pull-right"><b>Commissions in Pipeline', getPipeline('annual', QUOTE_PROVIDED, $year)];
        $rows[] = ['<span class="pull-right"><b>Sold', yearlyFor(SOLD, null, $year)];
        $rows[] = ['<span class="pull-right"><b>No Shows', yearlyFor(NO_SHOW, null, $year)];
        $rows[] = ['<span class="pull-right"><b>Waiting for Customer', yearlyFor(WAITING_FOR_CUSTOMER, null, $year)];
        $rows[] = ['<span class="pull-right"><b>Commissions Earned', getPipeline('annual', SOLD, $year)];
    }
    else
    {
        $rows = [];
        foreach (User::whereLevelId(4)->get() AS $user)
        {
            if (!$user->active) continue;
            $rows[] = ['<b>' . $user->name . "</b>"];
            $rows[] = [];
            $rows[] = [
                '<span class="pull-right"><b>Showroom Scheduled',
                yearlyFor(SHOWROOM_SCHEDULED, $user->id, $year)
            ];
            $rows[] = ['<span class="pull-right"><b>Quote Provided', yearlyFor(QUOTE_PROVIDED, $user->id, $year)];
            $rows[] = [
                '<span class="pull-right"><b>Commissions in Pipeline',
                getPipeline('annual', QUOTE_PROVIDED, $user->id, $year)
            ];
            $rows[] = ['<span class="pull-right"><b>Sold', yearlyFor(SOLD, $user->id, $year)];
            $rows[] = ['<span class="pull-right"><b>No Shows', yearlyFor(NO_SHOW, $user->id, $year)];
            $rows[] = [
                '<span class="pull-right"><b>Waiting for Customer',
                yearlyFor(WAITING_FOR_CUSTOMER, $user->id, $year)
            ];
            $rows[] = ['<span class="pull-right"><b>Commissions Earned', getPipeline('annual', SOLD, $user->id, $year)];
        }
    }
    return Table::init()->headers($headers)->rows($rows)->render();

}

/**
 * Show Monthly Table
 *
 * @return mixed
 */
function monthly()
{

    $headers = ['Item', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'YTD'];
    if (!Auth::user()->superuser)
    {
        $rows = [];
        $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', monthlyFor(SHOWROOM_SCHEDULED)];
        $rows[] = ['<span class="pull-right"><b>Quote Provided', monthlyFor(QUOTE_PROVIDED)];
        $rows[] = ['<span class="pull-right"><b>Commissions in Pipeline', getPipeline('year', QUOTE_PROVIDED)];
        $rows[] = ['<span class="pull-right"><b>Sold', monthlyFor(SOLD)];
        $rows[] = ['<span class="pull-right"><b>No Shows', monthlyFor(NO_SHOW)];
        $rows[] = ['<span class="pull-right"><b>Waiting for Customer', monthlyFor(WAITING_FOR_CUSTOMER)];
        $rows[] = ['<span class="pull-right"><b>Commissions Earned', getPipeline('year', SOLD)];
    }
    else
    {
        $rows = [];
        foreach (User::whereLevelId(4)->get() AS $user)
        {
            if (!$user->active) continue;
            $rows[] = ['<b>' . $user->name . "</b>"];
            $rows[] = [];
            $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', monthlyFor(SHOWROOM_SCHEDULED, $user->id)];
            $rows[] = ['<span class="pull-right"><b>Quote Provided', monthlyFor(QUOTE_PROVIDED, $user->id)];
            $rows[] = [
                '<span class="pull-right"><b>Commissions in Pipeline',
                getPipeline('year', QUOTE_PROVIDED, $user->id)
            ];
            $rows[] = ['<span class="pull-right"><b>Sold', monthlyFor(SOLD, $user->id)];
            $rows[] = ['<span class="pull-right"><b>No Shows', monthlyFor(NO_SHOW, $user->id)];
            $rows[] = ['<span class="pull-right"><b>Waiting for Customer', monthlyFor(WAITING_FOR_CUSTOMER, $user->id)];
            $rows[] = ['<span class="pull-right"><b>Commissions Earned', getPipeline('year', SOLD, $user->id)];
        }
    }
    return Table::init()->headers($headers)->rows($rows)->render();
}

/**
 * Get Commission Pipeline
 *
 * @param string $for week or year
 * @param        $status
 * @return null
 */
function getPipeline($for = 'week', $status, $user = null, $year = null)
{
    if (!$user)
    {
        $user = Auth::user()->id;
    }
    switch ($for)
    {
        case 'week' :
            for ($i = 0; $i <= 6; $i++)
            {
                $start = Carbon::now()->startOfWeek()->addDays($i);
                $end = Carbon::now()->startOfWeek()->addDays($i)->endOfDay();
                $total[$i] = 0;
                $leads = LeadUpdate::whereStatus($status)
                    //->groupBy('lead_id')
                    ->whereUserId($user)
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<=', $end)->get();
                foreach ($leads AS $lead)
                {
                    $lead = $lead->lead;
                    if ($lead->quotes)
                    {
                        if ($lead->quotes()->first())
                        {
                            if ($lead->quotes()->first()->for_designer == 0)
                            {
                                $total[$i] += QuoteGenerator::getQuoteObject($lead->quotes()->first())->forDesigner;
                            }
                            else
                            {
                                $total[$i] += $lead->quotes()->first()->for_designer;
                            }
                        }
                    }
                }
            }
            break;
        case 'year' :
            $grand = 0;
            for ($i = 0; $i <= 11; $i++)
            {
                $total[$i] = 0;
                $start = Carbon::now()->startOfYear()->addMonths($i);
                $end = Carbon::now()->startOfYear()->addMonths($i)->endOfMonth();
                $leads = LeadUpdate::whereStatus($status)
                    ->whereUserId($user)
                    //->groupBy('lead_id')
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<=', $end)
                    ->get();
                foreach ($leads AS $lead)
                {
                    $lead = $lead->lead;
                    if ($lead->quotes)
                    {
                        if ($lead->quotes()->first())
                        {
                            if ($lead->quotes()->first()->for_designer > 0)
                            {
                                $amt = $lead->quotes()->first()->for_designer;
                            }
                            else
                            {
                                $amt = QuoteGenerator::getQuoteObject($lead->quotes()->first())->forDesigner;
                            }
                            $total[$i] += $amt;
                            $grand += $amt;
                        }
                    }
                }
            }
            $total[99] = $grand;
            break;

        case 'annual' :
            $grand = 0;
            $i = $year;
                $total[$i] = 0;
                $start = Carbon::parse("{$i}-01-01");
                $end = Carbon::parse("{$i}-12-31");
                $leads = LeadUpdate::whereStatus($status)
                    ->whereUserId($user)
                    //->groupBy('lead_id')
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<=', $end)
                    ->get();
                foreach ($leads AS $lead)
                {
                    $lead = $lead->lead;
                    if ($lead->quotes)
                    {
                        if ($lead->quotes()->first())
                        {
                            if ($lead->quotes()->first()->for_designer > 0)
                            {
                                $amt = $lead->quotes()->first()->for_designer;
                            }
                            else
                            {
                                $amt = QuoteGenerator::getQuoteObject($lead->quotes()->first())->forDesigner;
                            }
                            $total[$i] += $amt;
                            $grand += $amt;
                        }
                    }
                }

            $total[99] = $grand;

            break;
    }

    return $total;
}


// -- Actual Page -- //


echo BS::title("Designer Dashboard", Auth::user()->name);

$tabs[] = [
    'class'   => 'active',
    'title'   => "<i class='fa fa-calendar'></i> Weekly",
    'content' => weekly()
];

$tabs[] = [
    'class'   => '',
    'title'   => "<i class='fa fa-calendar'></i> Monthly",
    'content' => monthly()
];

$tabs[] = [
    'class'   => '',
    'title'   => "<i class='fa fa-calendar'></i> Yearly",
    'content' => yearly()
];


$p = Panel::init('primary')->tabs($tabs)->header("Designer Totals")->content($tabs)->render();
$span = BS::span(12, $p);
echo \vl\libraries\bootstrap\Modal::init()->id('workModal')->onlyConstruct();
echo BS::row($span);
