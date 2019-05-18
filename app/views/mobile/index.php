<?php
echo BS::title("Frugal Mobile");
use Carbon\Carbon;
$schedules = JobSchedule::whereUserId(Auth::user()->id)->whereComplete(false)->get();
$headers = ['Job', 'On', 'Drawings'];
$rows = [];
foreach ($schedules AS $schedule)
{
  if ($schedule && $schedule->job && $schedule->job->quote)
  $rows[] = ["<a href='/mobile/schedule/$schedule->id/view'>{$schedule->job->quote->lead->customer->name}</a>",
    Carbon::parse($schedule->start)->format("m/d/y h:i a"),
    "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                data-original-title='Drawings' data-toggle='modal' data-target='#files' href='/quote/{$schedule->job->quote->id}/files'><i class='fa fa-image'></i></a> &nbsp; &nbsp;"
  ];
}
$table = Table::init()->headers($headers)->rows($rows)->dataTables()->responsive()->render();
$span = BS::span(12, $table);
echo BS::row($span);
echo Modal::init()->id('files')->onlyConstruct()->render();

