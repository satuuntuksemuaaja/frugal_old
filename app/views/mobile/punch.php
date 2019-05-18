<?php
echo BS::title("Frugal Mobile");
$fields = [];
$punches = Punch::whereDesignationId(Auth::user()->designation_id)->get();
$opts = [];
$opts[] = ['val' => null, 'text' => '-- Select --'];
$opts[] = ['val' => 'Yes', 'text' => 'Yes'];
$opts[] = ['val' => 'No', 'text' => 'No'];
foreach ($punches AS $punch)
  $fields[] = ['type' => 'select', 'var' => "p_$punch->id", 'opts' => $opts, 'text' => $punch->question];
$form = Forms::init()->id('punchForm')->url("/mobile/job/$job->id/punch")->elements($fields)->render();
$save = Button::init()->text("Save")->icon('save')->color('success post')->formid('punchForm')->render();
$span = BS::span(12, $form.$save);
echo BS::row($span);