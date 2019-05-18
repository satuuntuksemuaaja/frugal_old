<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 4/23/15
 * Time: 1:26 PM
 */
use Carbon\Carbon;
echo BS::title("Frugal Reports - Sold Report");

// Left side button s- right side date ranges.
$buttons = [];
$buttons[] = ['color' => 'primary', 'text' => 'Lead Sources', 'url' => "/reports", 'icon' => 'question'];
$buttons[] = ['color' => 'warning', 'text' => 'Cabinets', 'url' => "/report/cabinets", 'icon' => 'question'];
$buttons[] = ['color' => 'info', 'text' => 'Designers', 'url' => "/report/designers", 'icon' => 'question'];
$buttons[] = ['color' => 'success', 'text' => 'Sold Report', 'url' => "/report/sold", 'icon' => 'money'];

$left = BS::buttons($buttons);

$fields = [];
$fields[] = ['type' => 'input', 'var' => 'start', 'text' => 'Start:', 'val' => Session::get('start'), 'mask' => '99/99/99'];
$fields[] = ['type' => 'input', 'var' => 'end', 'text' => 'End:', 'val' => Session::get('end'), 'mask' => '99/99/99'];
$fields[] = ['type' => 'hidden', 'var' => 'redirect', 'val' => Request::url()];
$form = Forms::init()->id('rangeForm')->url("/reports")->elements($fields)->render();
$set = Button::init()->text("Set Date Range for Reports")->color('primary post')->formid('rangeForm')->icon('save')->render();
$right = $form. $set;

$left = BS::span(6, $left);
$right = BS::span(6, $right);

echo BS::row($left.$right);

// How many sold in time frame, how many quote provided - and lead source?
