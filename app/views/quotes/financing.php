<?php
echo BS::title("Financing options", $quote->lead->customer->name);
$meta = unserialize($quote->meta);
$meta = $meta['meta'];

$fields = [];
$all = $no = $partial = 'primary';

if (isset($meta['finance']))
{
    $all = ($meta['finance']['type'] == 'all') ? 'success' : 'primary';
    $no = ($meta['finance']['type'] == 'none') ? 'success' : 'primary';
    $partial = ($meta['finance']['type'] == 'partial') ? 'success' : 'primary';
}


$pre = "<p>This form is only to be used if the customer requests <b>100%</b> financing. This assumes that the customer is putting no money down.</p>";
$fields = [];
$opts = [];
$opts[] = ['val' => '12', 'text' => '0% for 12 Months (Wells Fargo)'];
$opts[] = ['val' => '65', 'text' => '9.9% for 65 Months (Wells Fargo)'];
if ($quote->final)
{
    $opts[] = ['val' => '12G', 'text' => '0% for 12 Months (GreenSky)'];
    $opts[] = ['val' => '84G', 'text' => '9.9% for 84 Months (GreenSky)'];
}
$fields[] = ['type' => 'select', 'text' => 'Select Financing Terms', 'opts' => $opts, 'var' => 'terms', 'span' => 6];
$form = Forms::init()->labelSpan(4)->id('allFinanceForm')->url("/quote/$quote->id/financing/all")->elements($fields)->render();
$save = Button::init()->centered()->text("Set 100% Financing Option")->formid('allFinanceForm')->color('primary post')
              ->icon('money')->render();

$panel = Panel::init($all)->header("100% Financing")->content($pre . $form)->footer($save)->render();
$left = BS::span(4, $panel);


$pre = "<p>This form is only to be used if the customer requests <b>NO</b> financing. This assumes that the customer is paying the total price via Cash, Check, or Credit Card.</p>";
$fields = [];
$opts = [];
$nocredit = (isset($meta['finance']['no_credit']) && $meta['finance']['no_credit'] > 0) ? $meta['finance']['no_credit'] : '0.00';
$nocash = (isset($meta['finance']['no_cash']) && $meta['finance']['no_cash'] > 0) ? $meta['finance']['no_cash'] : '0.00';
$opts[] = ['val' => 'credit', 'text' => 'Credit Card'];
$opts[] = ['val' => 'cash', 'text' => 'Cash/Check'];
$opts[] = ['val' => 'split', 'text' => 'Split Payment'];
$fields[] = ['type' => 'select', 'var' => 'method', 'opts' => $opts, 'text' => 'Select Method of Payment (IF 100%)', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'Total (if split) payment in Cash/Check:', 'pre' => '$', 'val' => $nocash, 'var' => 'no_cash', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'Total (if split) payment in Credit:', 'pre' => '$', 'val' => $nocredit, 'var' => 'no_credit', 'span' => 6];

$form = Forms::init()->labelSpan(4)->id('noFinanceForm')->url("/quote/$quote->id/financing/none")->elements($fields)->render();
$save = Button::init()->centered()->text("Set No Financing Option")->formid('noFinanceForm')->color('primary post')
              ->icon('money')->render();
$panel = Panel::init($no)->header("No Financing")->content($pre . $form)->footer($save)->render();
$middle = BS::span(4, $panel);


$pre = "<p>This form is only to be used if the customer requests <b>partial</b> financing. This assumes that the customer is putting down some money and financing the remainder of the balance.</p>";
$fields = [];
$dp = (isset($meta['finance']['downpayment']) && $meta['finance']['downpayment'] > 0) ? $meta['finance']['downpayment'] : '0.00';
$downcredit = (isset($meta['finance']['down_credit']) && $meta['finance']['down_credit'] > 0) ? $meta['finance']['down_credit'] : '0.00';
$downcash = (isset($meta['finance']['down_cash']) && $meta['finance']['down_cash'] > 0) ? $meta['finance']['down_cash'] : '0.00';

$fields[] = ['type' => 'input', 'text' => '<b>Total</b> Down Payment Amount', 'val' => $dp, 'pre' => '$', 'var' => 'downpayment', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'Down payment in Cash:', 'pre' => '$', 'val' => $downcash, 'var' => 'down_cash', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'Down payment in Credit:', 'pre' => '$', 'val' => $downcredit, 'var' => 'down_credit', 'span' => 6];
$opts = [];
$opts[] = ['val' => '12', 'text' => '0% for 12 Months (Wells Fargo)'];
$opts[] = ['val' => '65', 'text' => '9.9% for 65 Months (Wells Fargo)'];
if ($quote->final)
{
    $opts[] = ['val' => '12G', 'text' => '0% for 12 Months (GreenSky)'];
    $opts[] = ['val' => '84G', 'text' => '9.9% for 84 Months (GreenSky)'];
}

$fields[] = ['type' => 'select', 'text' => 'Select Financing Terms For Remainder', 'opts' => $opts, 'var' => 'terms', 'span' => 6];
$form = Forms::init()->labelSpan(5)->id('partialFinanceForm')->url("/quote/$quote->id/financing/partial")->elements($fields)->render();
$save = Button::init()->centered()->text("Set Partial Financing Option")->formid('partialFinanceForm')->color('primary post')
              ->icon('money')->render();
$panel = Panel::init($partial)->header("Partial Financing")->content($pre . $form)->footer($save)->render();
$right = BS::span(4, $panel);


echo BS::row($left . $middle . $right);
$options = null;
$options .= Button::init()->text("Quote Overview")->color('info btn-lg')->withoutGroup()->icon('share')
                  ->url("/quote/$quote->id/view")->render();
$options .= "</div>";


echo BS::row(BS::span(9, $options, 3));