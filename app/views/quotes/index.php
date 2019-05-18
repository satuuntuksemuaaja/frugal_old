<?php
echo BS::title("Quotes", "Customer Quotes");
$headers = ['Actions', 'Customer/Title', 'Designer', 'Age', 'Type', 'Lead Status'];
$rows = [];
if (Input::has('showAll'))
    $add = "?showAll=true";
else $add = null;
$table = Table::init()->headers($headers)->rows($rows)->dataTables("/quotes/ajax/list{$add}")->render();
$buttons[] = ['icon' => 'plus', 'color' => 'default', 'text' => 'Create Quote', 'modal' => 'create'];
$buttons[] = ['icon' => 'search', 'color' => 'primary', 'text' => 'Show All', 'url' => '/quotes?showAll=true'];
$add = BS::Buttons($buttons);
$span = BS::span(12, $add.$table);
echo BS::row($span);

// Create a new Quote
$leads = Lead::orderBy('id', 'DESC')->get();
foreach ($leads AS $lead)
  $opts[] = ['val' => $lead->id, 'text' => $lead->customer->name . " ({$lead->customer->city}, {$lead->customer->state}) ($lead->id)"];
//$opts = asort($opts);
$fields = [];
$pre = "<h5>You are creating an <b>initial</b> quote. Once you have completed the initial quote you will be able to
create a final quote</h5>";
$fields[] = ['type' => 'select2', 'var' => 'lead_id', 'opts' => $opts, 'span' => 6, 'text' => 'Select Customer:'];
$opts = [];
$opts[] = ['val' => 'Full Kitchen', 'text' => 'Full Kitchen', 'checked' => true];
$opts[] = ['val' => 'Cabinet Only', 'text' => 'Cabinet Only'];
$opts[] = ['val' => 'Cabinet and Install', 'text' => 'Cabinet and Install'];
$opts[] = ['val' => 'Granite Only', 'text' => 'Granite Only'];
if (Auth::user()->id == 1 || Auth::user()->id == 5)
    $opts[] = ['val' => 'Builder', 'text' => 'Builder'];

$fields[] = ['type' => 'radio', 'var' => 'type', 'opts' => $opts, 'text' => 'Quote Type:'];
$form = Forms::init()->id('newInitialForm')->labelSpan(4)->elements($fields)->url("/quotes/create")->render();
$save = Button::init()->text("Begin Quote")->color('success mpost')->formid('newInitialForm')->icon('save')->render();
$modal = Modal::init()->id('create')->header("Create Initial Quote")->content($pre.$form)->footer($save)->render();
echo $modal;
echo Modal::init()->id('files')->onlyConstruct()->render();
echo Modal::init()->id('workModal')->onlyConstruct()->render();