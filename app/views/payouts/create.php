<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/23/16
 * Time: 12:20 PM
 */
echo BS::title("Create New Payout", "Job $job->id");
$opts = [];
foreach (Designation::whereActive(true)->get() as $des)
    $opts[] = ['val' => $des->id, 'text' => $des->name];
$fields[] = ['type' => 'select', 'var' => 'designation_id', 'opts' => $opts, 'text' => 'Designation: '];
$fields[] = ['type' => 'hidden', 'var' => 'job_id', 'val' => $job->id];
$fields[] = ['type' => 'submit', 'var' => 'createPayout', 'val' => 'Create Payout', 'class' => 'btn-info'];
$form = Forms::init()->id('primaryForm')->labelSpan(4)->elements($fields)->url("/payouts/create")->render();
$panel = Panel::init('default')->header("Create Payout")->content($form)->render();
$left = BS::span(6, "<a class='btn btn-primary' href='/payouts'>Back to Payouts</a>". $panel);
echo BS::row($left);