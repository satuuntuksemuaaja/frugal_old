<?php
use Carbon\Carbon;

echo BS::title("Cabinet Reports");

// Left side button s- right side date ranges.
$buttons = [];
$buttons[] = ['color' => 'primary', 'text' => 'Lead Sources', 'url' => "/reports", 'icon' => 'question'];
$buttons[] = ['color' => 'warning', 'text' => 'Cabinets', 'url' => "/report/cabinets", 'icon' => 'question'];
$buttons[] = ['color' => 'info', 'text' => 'Designers', 'url' => "/report/designers", 'icon' => 'question'];
if (Auth::user()->id == 1 || Auth::user()->id == 5)
{
    $buttons[] = ['color' => 'info', 'text' => 'Frugal Report', 'url' => "/report/frugal", 'icon' => 'money'];
}

$left = BS::buttons($buttons);

$fields = [];
$fields[] = ['type' => 'input', 'var' => 'start', 'text' => 'Start:', 'val' => Session::get('start'), 'mask' => '99/99/99'];
$fields[] = ['type' => 'input', 'var' => 'end', 'text' => 'End:', 'val' => Session::get('end'), 'mask' => '99/99/99'];
$fields[] = ['type' => 'hidden', 'var' => 'redirect', 'val' => Request::url()];
$form = Forms::init()->id('rangeForm')->url("/reports")->elements($fields)->render();
$set = Button::init()->text("Set Date Range for Reports")->color('primary post')->formid('rangeForm')->icon('save')->render();
$right = $form . $set;

$left = BS::span(6, $left);
$right = BS::span(6, $right);

echo BS::row($left . $right);

$quotes = Quote::whereAccepted(true)->get();
$cabtally = [];
foreach ($quotes AS $quote)
{
    if (Session::has('start') && $quote->created_at < Carbon::parse(Session::get('start'))) continue;
    if (Session::has('end') && $quote->created_at > Carbon::parse(Session::get('end'))) continue;
    foreach ($quote->cabinets AS $cabinet)
    {
        if (empty($cabtally[$cabinet->cabinet_id]))
            $cabtally[$cabinet->cabinet_id] = 0;
        $cabtally[$cabinet->cabinet_id] = $cabtally[$cabinet->cabinet_id] + 1;
    }
    
}
$headers = ['Cabinet', 'Count'];
foreach (Cabinet::all() as $cabinet)
{
    $rows[] = [$cabinet->name, (isset($cabtally[$cabinet->id])) ? $cabtally[$cabinet->id] : 0];
}

$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
echo BS::span(6, $table);

