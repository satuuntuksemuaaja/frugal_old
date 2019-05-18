<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/15/15
 * Time: 6:59 AM
 */
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


$year = Carbon::now()->year;
$start = Carbon::parse(Session::get('start'));
$end = Carbon::parse(Session::get('end'));
$headers = ['Customer', 'Lead Created'];
$rows = [];
$source = LeadSource::find($source);
foreach ($source->leads()->where('created_at', '>=', $start)->where('created_at', '<=', $end)->get() AS $lead)
{
    switch ($type)
    {
        case 'count' :
            $rows[] = [
              "<a href='/profile/$lead->customer_id/view'>{$lead->customer->name}</a>",
              $lead->created_at->format("m/d/y")
            ];
            break;
        case 'sold' :
            foreach ($lead->quotes as $quote)
            {
                if ($quote->accepted)
                    $rows[] = [
                        "<a href='/profile/$lead->customer_id/view'>{$lead->customer->name}</a>",
                        $lead->created_at->format("m/d/y")
                    ];
            }

            break;
        case 'provided' :
            if ($lead->provided)
                $rows[] = [
                    "<a href='/profile/$lead->customer_id/view'>{$lead->customer->name}</a>",
                    $lead->created_at->format("m/d/y")
                ];
            break;
    }
}
$table = Table::init()->headers($headers)->rows($rows)->render();
echo Modal::init()->isInline()->header("Report for {$source->type}")->content($table)->footer(null)->render();
