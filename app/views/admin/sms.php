<?php
$headers = ['Timestamp', 'From', 'Message'];
$rows = [];
foreach ($user->smses AS $sms)
{
  $rows[] = [$sms->created_at->format('m/d/y h:i a'), $sms->source, $sms->message];
}
$table = Table::init()->headers($headers)->rows($rows);
$span = BS::span(12, $table);
$row = BS::row($span);
echo Modal::init()->isInline()->header("Incoming SMS for $user->name")->content($row);
