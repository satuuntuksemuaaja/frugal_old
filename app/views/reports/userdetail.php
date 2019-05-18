<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 3/27/16
 * Time: 10:09 AM
 */
use Carbon\Carbon;

$user = User::find($user);
$headers = ['Customer', 'Quote', 'Type'];
$rows = [];
$sold = 0;
$provided = 0;
$providedBlock = [];
$soldBlock = [];
$rtype = Session::get('rtype') ?: "All Job Types";

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
        if ($quote->lead->status_id == 10 || $quote->lead->provided)
        {
            $provided++;
            $providedBlock[] = [
                "<a href='/profile/{$quote->lead->customer->id}/view'>{$quote->lead->customer->name}</a>",
                "<a href='/quote/{$quote->id}/view'>#{$quote->id}</a>",
                $quote->type
            ];
        }
        $count++;
        if ($quote->accepted)
        {
            if ($rtype != 'All Job Types')
            {
                if ($quote->type == $rtype && $quote->accepted)
                {
                    $sold++;
                    $soldBlock[] = [
                        "<a href='/profile/{$quote->lead->customer->id}/view'>{$quote->lead->customer->name}</a>",
                        "<a href='/quote/{$quote->id}/view'>#{$quote->id}</a>",
                        $quote->type
                    ];
                }
            }
            else
            {
                if ($quote->accepted)
                {
                    $sold++;
                    $soldBlock[] = [
                        "<a href='/profile/{$quote->lead->customer->id}/view'>{$quote->lead->customer->name}</a>",
                        "<a href='/quote/{$quote->id}/view'>#{$quote->id}</a>",
                        $quote->type
                    ];
                }
            }


        }
    } // fe lead


if ($type == 'sold')
    $rows = $soldBlock;
else $rows = $providedBlock;
$total = ($type == 'sold') ? $sold : $provided;
$table = Table::init()->headers($headers)->rows($rows)->render();
echo Modal::init()->isInline()->header("Report for {$user->name}")->content($table)->footer("Total: $total")->render();
