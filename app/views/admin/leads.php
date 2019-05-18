<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 6/3/15
 * Time: 1:50 PM
 */

use vl\quotes\QuoteGenerator;

$pre = "<h3>Lead Settings</h3>";

$quote = new QuoteGenerator(new Quote); // Our get setting routine is in here.

$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Days before Follow-up Icon is Red:', 'var' => 'lead_red', 'val' => $quote->getSetting('lead_red'), 'span' => 2];

$fields[] = ['type' => 'input', 'text' => 'Days before Sending Follow-up E-mail:', 'var' => 'lead_followup', 'val' => $quote->getSetting('lead_followup'), 'span' => 2];
$fields[] = ['type' => 'textarea', 'text' => 'Follow-up Email Content:', 'var' => 'lead_followup_content',
             'val' => $quote->getSetting('lead_followup_content'), 'span' => 8];

$fields[] = ['type' => 'input', 'text' => 'Days before Sending Warning E-mail:', 'var' => 'lead_warning', 'val' => $quote->getSetting('lead_warning'), 'span' => 2];
$fields[] = ['type' => 'textarea', 'text' => 'Warning Email Content:', 'var' => 'lead_warning_content', 'val' => $quote->getSetting('lead_warning_content'), 'span' => 8];

$form = Forms::init()->labelspan(4)->id('leadSettings')->url('/admin/leads')->elements($fields)->render();
$saveButton = Button::init()->color('primary post')->icon('save')->text('Save Lead Settings')
                    ->message('Saving..')->formid('leadSettings')->render();

$span = BS::span(8, $pre . $form . $saveButton);
echo BS::row($span);