<?php
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Select Start Date:', 'var' => 'start', 'span' => 3, 'class' => 'dp',
              'val' => Input::get('start')];
$fields[] = ['type' => 'submit', 'text' => 'Generate Report', 'class' => 'btn btn-primary', 'var' => 'submit'
  , 'val' => 'Generate Report'];
$form = Forms::init()->id('dateForm')->elements($fields)->url("/jobs/export")->render();
$span = BS::span(12, $form);
echo BS::row($span);



echo BS::encap("$('.dp').datepicker({format: 'mm/dd/yyyy'});");
