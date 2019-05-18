<?php
$fields = [];
$pre = "<h5>You are creating an <b>initial</b> quote. Once you have completed the initial quote you will be able to
create a final quote</h5>";
$fields[] = ['type' => 'input', 'var' => 'lead', 'span' => 6, 'text' => 'Customer:', 'val' => $lead->customer->name,
	'disabled' => true];
$fields[] = ['type' => 'hidden', 'var' => 'lead_id', 'val' => $lead->id];
$opts = [];
$opts[] = ['val' => 'Full Kitchen', 'text' => 'Full Kitchen', 'checked' => true];
$opts[] = ['val' => 'Cabinet Only', 'text' => 'Cabinet Only'];
$opts[] = ['val' => 'Cabinet Small Job', 'text' => 'Cabinet Small Job'];
$opts[] = ['val' => 'Cabinet and Install', 'text' => 'Cabinet and Install'];
$opts[] = ['val' => 'Granite Only', 'text' => 'Granite Only'];
if (Auth::user()->id == 1 || Auth::user()->id == 5)
    $opts[] = ['val' => 'Builder', 'text' => 'Builder'];

$fields[] = ['type' => 'radio', 'var' => 'type', 'opts' => $opts, 'text' => 'Quote Type:'];
$form  = Forms::init()->id('newInitialForm')->labelSpan(4)->elements($fields)->url("/quotes/create")->render();
$save  = Button::init()->text("Begin Quote")->color('success mpost')->formid('newInitialForm')->icon('save')->render();
$modal = Modal::init()->isInline()->header("Create Initial Quote")->content($pre.$form)->footer($save)->render();
echo $modal;