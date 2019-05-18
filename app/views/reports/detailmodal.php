<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/15/15
 * Time: 6:59 AM
 */
use Carbon\Carbon;

$user = User::find($user);
$year = Carbon::parse(Session::get('end'))->year;

$date = "{$month}/1/{$year}";
$start = Carbon::parse($date);
$end = Carbon::parse($date)->addMonth();
$headers = ['Customer', 'Quote Created', 'Job Created', 'Amount'];
if (Request::get('profit'))
    $headers[] = "Profit";

$rows = [];
foreach ($user->quotes AS $quote)
{
    if (!$quote->job) continue;
    if ($quote->job->created_at < $start) continue;
    if ($quote->job->created_at > $end) continue;
    $obj = \vl\quotes\QuoteGenerator::getQuoteObject($quote);

    $rows[] = [
        "<a href='/profile/{$quote->lead->customer->id}/view'>{$quote->lead->customer->name}</a>",
        "<a href='/quote/{$quote->id}/view'>{$quote->created_at->format("m/d/y")}</a>",
        $quote->job->created_at->format("m/d/y"),
        "$". number_format($quote->finance_total,2),
        Request::get('profit') ? number_format(
            ($obj->forFrugal + 150.00 + $obj->cabinetBuildup) - ($obj->discounts),2) : null
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
echo Modal::init()->isInline()->header("Report for {$user->name} for Month {$month}")->content($table)->footer(null)->render();
