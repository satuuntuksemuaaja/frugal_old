<?php
use vl\quotes\QuoteGenerator;

$quote = new QuoteGenerator(new Quote);

$span = [];
$fields[] = ['type' => 'raw', 'raw' => "<h4>Designer Amounts</h4>"];
$fields[] = ['type' => 'input', 'text' => '< 35 Items', 'var' => 'dL35', 'val' => $quote->getSetting('dL35')]; // Designer Less than 35
$fields[] = ['type' => 'input', 'text' => '> 35 and <= 55', 'var' => 'dG35L55', 'val' => $quote->getSetting('dG35L55')];
$fields[] = ['type' => 'input', 'text' => '> 55 and <= 65', 'var' => 'dG55L65', 'val' => $quote->getSetting('dG55L65')];
$fields[] = ['type' => 'input', 'text' => '> 65 and <= 75', 'var' => 'dG65L75', 'val' => $quote->getSetting('dG65L75')];
$fields[] = ['type' => 'input', 'text' => '> 75 and <= 85', 'var' => 'dG75L85', 'val' => $quote->getSetting('dG75L85')];
$fields[] = ['type' => 'input', 'text' => '> 85 and <= 94', 'var' => 'dG85L94', 'val' => $quote->getSetting('dG85L94')];
$fields[] = ['type' => 'input', 'text' => '> 94 and <= 110', 'var' => 'dG94L110', 'val' => $quote->getSetting('dG94L110')];
$fields[] = ['type' => 'input', 'text' => '> 110', 'var' => 'dG110', 'val' => $quote->getSetting('dG110')];
$span[] = ['span' => 4, 'elements' => $fields];

$fields = [];
$fields[] = ['type' => 'raw', 'raw' => "<h4>Frugal Amounts</h4>"];
$fields[] = ['type' => 'input', 'text' => '< 35 Items', 'var' => 'fL35', 'val' => $quote->getSetting('fL35')]; // Designer Less than 35
$fields[] = ['type' => 'input', 'text' => '> 35 and <= 55', 'var' => 'fG35L55', 'val' => $quote->getSetting('fG35L55')];
$fields[] = ['type' => 'input', 'text' => '> 55 and <= 65', 'var' => 'fG55L65', 'val' => $quote->getSetting('fG55L65')];
$fields[] = ['type' => 'input', 'text' => '> 65 and <= 75', 'var' => 'fG65L75', 'val' => $quote->getSetting('fG65L75')];
$fields[] = ['type' => 'input', 'text' => '> 75 and <= 85', 'var' => 'fG75L85', 'val' => $quote->getSetting('fG75L85')];
$fields[] = ['type' => 'input', 'text' => '> 85 and <= 94', 'var' => 'fG85L94', 'val' => $quote->getSetting('fG85L94')];
$fields[] = ['type' => 'input', 'text' => '> 94 and <= 110', 'var' => 'fG94L110', 'val' => $quote->getSetting('fG94L110')];
$fields[] = ['type' => 'input', 'text' => '> 110', 'var' => 'fG110', 'val' => $quote->getSetting('fG110')];
$span[] = ['span' => 4, 'elements' => $fields];

$fields = [];
$fields[] = ['type' => 'raw', 'raw' => "<h4>Vendor Payout Amounts</h4>"];
$fields[] = ['type' => 'input', 'text' => 'For Electrician', 'var' => 'fElectrician', 'val' => $quote->getSetting('fElectrician')];
$fields[] = ['type' => 'input', 'text' => 'For Plumber', 'var' => 'fPlumber', 'val' => $quote->getSetting('fPlumber')];
$fields[] = ['type' => 'textarea', 'text' => 'XML Ignore List', 'var' => 'xmlignore', 'val' => $quote->getSetting('xmlignore')];

$span[] = ['span' => 4, 'elements' => $fields];
$fields = [];
$fields[] = ['type' => 'textarea', 'text' => 'Common Sense Page:', 'var' => 'commonSense', 'val' => $quote->getSetting('commonSense')];
$span[] = ['span' => 10, 'elements' => $fields];

$form = Forms::init()->labelspan(4)->id('modifications')->url('/admin/dynamic')->span($span)->render();
$saveButton = Button::init()->color('primary post')->icon('save')->text('Save Modifications')
                    ->message('Saving Notifications..')->formid('modifications')->render();

$span = BS::span(12, $form . $saveButton);
echo BS::row($span);