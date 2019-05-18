<?php
/**
 * Short Description
 *
 * @package: fk2
 * @author: Chris Horne {chris@vocalogic.com}
 * @since: 6/8/16 - 7:54 AM
 */

$headers = ['Lead', 'Updated', 'Status', 'Quote', 'Commission Amount'];
$rows = [];
$ttl = 0;
foreach ($updates AS $update)
{
    $follow = ($update->lead && $update->lead->followups()->count() > 0)
        ? "<a href='/lead/{$update->lead->id}/followups'><i class='fa fa-phone'></i></a>"
        : null;

    $rows[] = [
        "$follow ({$update->lead->id}) " . $update->lead->customer->name,
        $update->created_at->format("m/d/y h:i a"),
        $update->newstatus ? $update->newstatus->name : "Removed Status",
        $update->lead->quotes()->first() ? "<a href='/quote/".$update->lead->quotes()->first()->id."/view'>".$update->lead->quotes()->first()->id."</a>" : null,
        $update->lead->quotes()->first() ? "$" . number_format($update->lead->quotes()->first()->for_designer,2) : 'N/A',
    ];
    $ttl += $update->lead && $update->lead->quotes() && $update->lead->quotes()->first() ? $update->lead->quotes()->first()->for_designer : 0;
}
$rows[] = [
    "<span class='pull-right'><B>TOTAL: </B>",
    "$" . number_format($ttl,2),
    null,
    null,
    null
];
$table = Table::init()->headers($headers)->rows($rows)->render();
echo Modal::init()->isInline()->header("Detail")->content($table)->footer(null)->render();
