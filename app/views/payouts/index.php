<?php
use Carbon\Carbon;
use vl\quotes\QuoteGenerator;

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 11/19/16
 * Time: 5:00 PM
 */
function generatePayout($job, $designation)
{
    $quote = $job->quote()->whereAccepted(true)->first();
    if (!$quote) return "Quote Not Found. Really old job?";
    if ($designation == 8) // Designers and delivery share.. ugh.
    {
        $uid = $job->quote->lead->user_id;
    }
    else
    {
        $schedule = $job->schedules()->whereDesignationId($designation)->first();
        if (!$schedule) return null; // No Assignment
        $uid = $schedule->user_id;
    }
    $details = QuoteGenerator::getQuoteObject($quote);
    switch ($designation)
    {
        case 4 : // Installer
            $amount = $details->forInstaller;
            break;
        case 1 : // Plumber
            $amount = $details->forPlumber;
            break;
        case 2 : // Electrician
            $amount = $details->forElectrician;
            break;
        case 8 : // Designer
            $amount = $details->forDesigner;
            break;
        case 11 : // Flooring Contractor
            $amount = $details->tile;
            break;
        default :
            $amount = 0;
            break;

    }

    $payout = Payout::create([
        'job_id'         => $job->id,
        'paid'           => 0,
        'archived'       => 0,
        'approved'       => 0,
        'user_id'        => $uid,
        'designation_id' => $designation,
        'total'          => $amount ?: 0
    ]);
    $debugs = $details->debug;
    foreach ($debugs AS $debug)
    {
        switch ($designation)
        {

            case 4 : // Cabinet Installer
                if (preg_match('/installer/i', $debug[0]) && !preg_match('/applying/i', $debug[0]))
                {
                    (new PayoutItem)->create([
                        'payout_id' => $payout->id,
                        'item'      => $debug[0],
                        'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                    ]);
                }
                break;
            case 8 : // Designer
                if (preg_match('/designer/i', $debug[0]) && !preg_match('/applying/i', $debug[0]))
                {
                    (new PayoutItem)->create([
                        'payout_id' => $payout->id,
                        'item'      => $debug[0],
                        'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                    ]);
                }
                break;
            case 1 : // Plumber
                if (preg_match('/plumber/i', $debug[0]) && !preg_match('/applying/i', $debug[0]))
                {
                    (new PayoutItem)->create([
                        'payout_id' => $payout->id,
                        'item'      => $debug[0],
                        'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                    ]);
                }
                break;
            case 2 :  // Electrician
                if (preg_match('/electrician|electrican/i', $debug[0]) && !preg_match('/applying/i', $debug[0]))
                {
                    (new PayoutItem)->create([
                        'payout_id' => $payout->id,
                        'item'      => $debug[0],
                        'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                    ]);
                }
                break;
            case 11 : // Flooring
                if (preg_match('/tile/i', $debug[0]))
                {
                    (new PayoutItem)->create([
                        'payout_id' => $payout->id,
                        'item'      => $debug[0],
                        'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                    ]);
                }
                break;
        }
    }
    // Create line items.

}

function getPayouts($job, $designation)
{
    $dtext = [
        4  => 'Cabinet Installer',
        1  => 'Plumber',
        2  => 'Electrician',
        8  => 'Designer',
        11 => 'Flooring Contractor',
        3  => 'Granite Company'
    ];

    $quote = $job->quote()->whereAccepted(true)->first();
    if (!$quote) return [null, null, null, "No Quote found - Must be really old job!", null, null, null, null, null];
    $payout = Payout::whereJobId($job->id)->whereDesignationId($designation)->first();
    if (!$payout)
    {
        $payout = generatePayout($job, $designation);
        if (!$payout)
        {
            return [null, null, null, null, "No $dtext[$designation] found to pay.", null, null, null, null, null];
        }
    }
    $approval = (Auth::user()->id == 5 || Auth::user()->id == 1) ? "<a href='/payouts/$payout->id?approve=true'><span style='color: red'>No</span></a>" : "<span style='color: red'>No</span>";

    return [
        null,
        null,
        null,
        null,
        $payout->user && $payout->user->designation ? $payout->user->designation->name . " - " . "<a href='/payouts/report/$payout->user_id'>{$payout->user->name}</a>" : "No User Found",
        "<a href='/payouts/$payout->id'>$" . number_format($payout->total, 2) . "</a>",
        $payout->invoice ?: "None",
        $payout->check ?: "None",
        $payout->approved ? "<span style='color: green'>Yes</span>" : $approval,
        $payout->paid ? "<span style='color: green'>" . Carbon::parse($payout->paid_on)
                ->format("m/d/y") . "</span>" : "<span style='color: red'>No</span>"
    ];
}

function getPayout($id)
{
    $id = $id[0];
    $payout = Payout::find($id);
    if (!$payout) return [];
    $approval = (Auth::user()->id == 5 || Auth::user()->id == 1) ? "<a href='/payouts/$payout->id?approve=true'><span style='color: red'>No</span></a>" : "<span style='color: red'>No</span>";
    $des_name = $payout->user ? $payout->user->designation->name : "No Designation";
    $user_name = $payout->user ? $payout->user->name : "Unassigned";
    return [
        null,
        null,
        null,
        null,
        $des_name . " - " . "<a href='/payouts/report/$payout->user_id'>{$user_name}</a>",
        "<a href='/payouts/$payout->id'>$" . number_format($payout->total, 2) . "</a>",
        $payout->invoice ?: "None",
        $payout->check ?: "None",
        $payout->approved ? "<span style='color: green'>Yes</span>" : $approval,
        $payout->paid ? "<span style='color: green'>" . Carbon::parse($payout->paid_on)
                ->format("m/d/y") . "</span>" : "<span style='color: red'>No</span>"
    ];
}

echo BS::title("Payout Manager", "All Unpaid Jobs");
$headers = ['Customer', 'Type', 'Job Date', 'Signed', 'Contractor', 'Amount', 'Invoice', 'Check', 'Approved', 'Paid'];
$rows = [];
if (Input::has('all'))
{
    $jobs = Job::with('fft')->wherePaid(true)->where('start_date', '>', '2016-01-01')->orderBy('created_at', 'DESC')
        ->get();
}
else
{
    $jobs = Job::with('fft')->wherePaid(false)->where('start_date', '>', '2016-01-01')->orderBy('created_at', 'DESC')
        ->get();
}
$jobs = $jobs->sortBy('start_date');
foreach ($jobs as $job)
{
    if (!$job->quote) continue;
    $customer = @$job->quote->lead->customer->name ?: "Unknown Customer Association";
    $archive = true;
    foreach (Payout::whereJobId($job->id)->get() as $p)
    {
        if (!$p->paid)
            $archive = false;
    }
    if (Auth::user()->id == 1 || Auth::user()->id == 5 || Auth::user()->id == 7) $archive = true;
    if ($archive)
        $archive = "<span class='pull-right'><a class='get' href='/job/$job->id/paid'><i class='fa fa-archive'></a></span>";
    $rows[] = [
        "<a name='$job->id'>$customer</a> $archive",
        $job->quote->type,
        Carbon::parse($job->start_date)->format("m/d/y"),
        Carbon::parse($job->fft->signoff_stamp)->timestamp > 0 ? Carbon::parse($job->fft->signoff_stamp)
            ->format("m/d/y") : "Not Signed",
        "<span class='pull-right'><a href='/payouts/create?withJob=$job->id'>+ new</a></span>",
        null,
        null,
        null,
        null,
        null
    ];
    $rows[] = getPayouts($job, 8); // Designer
    $rows[] = getPayouts($job, 4); // Installer
    $rows[] = getPayouts($job, 1); // Plumber
    $rows[] = getPayouts($job, 2); // Electrician
    $rows[] = getPayouts($job, 11); // Flooring Contractor
    $rows[] = getPayouts($job, 3); // Granite
    if ($job->payout_additionals)
    {
        $adds = unserialize($job->payout_additionals);
        foreach ($adds as $add)
        {
            $rows[] = getPayout($add);
        }
    }


}
$table = Table::init()->headers($headers)->rows($rows)->render();
$action = Input::has('all') ? "<a href='/payouts'>Show Unpaid</a>" : "<a href='/payouts?all=true'>Show Archived</a>";
$panel = Panel::init('default')->header("Job List - $action")->content($table, false)->render();
$left = BS::span(12, $panel);
echo BS::row($left);
