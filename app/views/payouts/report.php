<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/20/16
 * Time: 3:08 PM
 */
use Carbon\Carbon;


echo BS::title("Payout for $user->name", "Create Report");
$start = Carbon::now()->subDays(30);
$headers = [null, 'Customer', 'Start Date', 'Paid On', 'Check #', 'Total', 'Items'];
$rows = [];
echo "<form method='post' action='/payouts/report/$user->id'>";
foreach (Payout::whereUserId($user->id)->where('paid_on', '>=', $start)->get() as $payout)
{
    $items = null;
    foreach ($payout->items as $item)
    {
        $items .= $item->item . " ($item->amount), ";
    }

        $rows[] = [
            "<input type='checkbox' name='p_$payout->id'>",
        $payout->job->quote->lead->customer->name,
        $payout->job->start_date,
        $payout->paid_on,
        $payout->check,
        $payout->total,
        $items
    ];
}
echo Table::init()->headers($headers)->rows($rows)->render();
echo "<input type='submit' class='btn btn-success' value='Create Report'>";
echo "</form>";
