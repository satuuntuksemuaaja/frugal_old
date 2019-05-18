<?php
echo BS::title("Additional Requirements", $quote->lead->customer->name);
$meta = unserialize($quote->meta);
$meta = $meta['meta'];
$fields = [];

$fields[] = ['type' => 'textarea', 'span' => 6,
              'text' => 'For <strong>Miscellaneous items</strong>, Enter each additional item per line like:
              (<b>Item 1 - 450.00</b>)',
              'var' => 'quote_misc', 'val' => (isset($meta['quote_misc'])) ? $meta['quote_misc'] : null];
$fields[] = ['type' => 'textarea', 'span' => 6,
              'text' => 'For <strong>Plumbing items</strong>,  Enter each additional item per line like:
              (<b>Item 1 - 450.00</b>)', 'var' => 'quote_plumbing_extras',
              'val' => (isset($meta['quote_plumbing_extras'])) ? $meta['quote_plumbing_extras'] : null];
$fields[] = ['type' => 'textarea', 'span' => 6,
              'text' => 'For <strong>Electrical items</strong>,  each additional item per line like:
              (<b>Item 1 - 450.00</b>)',
              'var' => 'quote_electrical_extras',
              'val' => (isset($meta['quote_electrical_extras'])) ? $meta['quote_electrical_extras'] : null];
$fields[] = ['type' => 'textarea', 'span' => 6,
              'text' => 'For <strong>Installer items</strong>, Enter each additional item per line like:
              (<b>Item 1 - 450.00</b>)',
              'var' => 'quote_installer_extras',
              'val' => (isset($meta['quote_installer_extras'])) ? $meta['quote_installer_extras'] : null];
$fields[] = ['type' => 'textarea', 'span' => 6,
              'text' => 'Special Requirements/Instructions',
              'var' => 'quote_special',
              'comment' => 'The information listed here will appear on page 6 of the contract to the customer',
              'val' => (isset($meta['quote_special'])) ? $meta['quote_special'] : null];
$fields[] = ['type' => 'input', 'span' => 6,
              'text' => 'Coupon (if any)',
              'var' => 'quote_coupon',
              'val' => (isset($meta['quote_coupon'])) ? $meta['quote_coupon'] : null];
$fields[] = ['type' => 'input', 'span' => 6,
              'text' => 'Additional Discount Amount (if any)',
              'var' => 'quote_discount',
              'val' => (isset($meta['quote_discount'])) ? $meta['quote_discount'] : null];
$fields[] = ['type' => 'textarea', 'span' => 6,
              'text' => 'Discount Reason (if additional discounts)',
              'var' => 'quote_discount_reason',
              'val' => (isset($meta['quote_discount_reason'])) ? $meta['quote_discount_reason'] : null];
$opts = [];
if ($quote->promotion_id)
    $opts[] = ['val' => $quote->promotion_id, 'text' => $quote->promotion->name];

$opts[] = ['val' => 0, 'text' => "-- Select Promotion --"];
foreach (Promotion::whereActive(true)->get() as $promo)
    $opts[] = [
        'val' => $promo->id,
        'text' => $promo->name
    ];
$fields[] = ['type' => 'select', 'span' => 6,
             'text' => 'Select Promotion',
             'var' => 'promotion_id',
             'opts' => $opts];

    $form = Forms::init()->id('primaryForm')->labelSpan(4)->elements($fields)->url("/quote/$quote->id/additional")->render();
$save = "<center>" . Button::init()->text("Save Requirements")->color('danger post pulse-red')->formid('primaryForm')
  ->icon('save')->render()."</center>";
$panel = Panel::init('primary')->header("Additional Requirements")->content($form)->footer($save)->render();
$left = BS::span(10, $panel,1);
echo BS::row($left);




$pass = true;
$options = "<div class='btn-group'>";
$options .= Button::init()->text("Review Hardware")->color('warning btn-lg')->withoutGroup()->icon('arrow-left')
  ->url("/quote/$quote->id/hardware")->render();
if (!isset($meta['progress_additional'])) $pass = false;

if ($pass)
  {
    if ($quote->type != 'Cabinet Only' && $quote->type != 'Cabinet and Install' && $quote->type != 'Builder')
    $options .= Button::init()->text("Next")->color('success btn-lg ')->withoutGroup()->icon('arrow-right')
  ->url("/quote/$quote->id/questionaire")->render();

 }
$options .= Button::init()->text("Quote Overview")->color('info btn-lg')->withoutGroup()->icon('share')
  ->url("/quote/$quote->id/view")->render();
$options .= "</div>";


echo BS::row(BS::span(9, $options, 3));
