<?php
use Carbon\Carbon;

$now = Carbon::now();
$pre = "<h4>Lead Updates</h4>";
$headers = ['Timestamp', 'Lead', 'Updated By', 'Note'];
$rows = [];
foreach (LeadNote::orderBy('created_at', 'DESC')->take(100)->get() as $note)
{
    if (!$note->lead || !$note->lead->customer) continue;
    $rows[] = [$note->created_at->format("m/d/y h:i a"),
        "<a href='/profile/{$note->lead->customer->id}/view'>{$note->lead->customer->name}</a>",
        $note->user->name,
        nl2br($note->note)];
}
$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$leadUpdates = $pre . $table;

// Now show Leads In Followup Required State
$pre = "<h4>Leads Requiring Followup</h4>";
$headers = ['Lead', 'Status', 'Lead Owner', 'Last Updated'];
$rows = [];
foreach (Lead::whereClosed(false)->whereWarning('Y')->get() as $lead)
{
    if (!$lead->user) continue;
    if ($lead->archived) continue;
    if ($lead->status_id == 14) continue; // Sold
    $rows[] = [
        "<a class='get tooltiped' data-original-title='Archive' href='/lead/$lead->id/archive'><i class='fa fa-eraser'></i></a> <a href='/profile/{$lead->customer->id}/view'>{$lead->customer->name}</a>",
        $lead->status ? $lead->status->name : 'New',
        $lead->user->name,
        $lead->last_note->timestamp > 0 ? $lead->last_note->format("m/d/y") : "Never",
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$leadFollowup = $pre . $table;


// Now show Leads In Followup Required State
$pre = "<h4>Leads In Warning State</h4>";
$headers = ['Lead', 'Lead Owner', 'Last Updated'];
$rows = [];
$headers = ['Lead', 'Status', 'Lead Owner', 'Last Updated'];
$rows = [];
foreach (Lead::whereClosed(false)->whereWarning('R')->get() as $lead)
{
    if (!$lead->user) continue;
    if ($lead->archived) continue;
    if ($lead->status_id == 14) continue; // Sold
    $rows[] = [
        "<a class='get tooltiped' data-original-title='Archive' href='/lead/$lead->id/archive'><i class='fa fa-eraser'></i></a> <a href='/profile/{$lead->customer->id}/view'>{$lead->customer->name}</a>",
        $lead->status ? $lead->status->name : 'New',
        $lead->user->name,
        $lead->last_note->timestamp > 0 ? $lead->last_note->format("m/d/y") : "Never",
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$leadWarning = $pre . $table;



$span = BS::span(6, $leadUpdates . $leadFollowup . $leadWarning);

echo BS::row($span);