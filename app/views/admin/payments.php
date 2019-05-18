<?php
$data = null;
if (Input::has('trans'))
echo BS::alert("success", "Transaction Successful", "Transacton ID: " . Input::get('trans'));

 $opts[] = ['val' => 'C', 'text' => 'Credit Card'];
    $opts[] = ['val' => 'A', 'text' => 'ACH/Checking'];
    $fields[] = ['type' => 'select', 'var' => 'type', 'class'=> 'type', 'opts' => $opts, 'text' => 'Account Type:'];
    $fields[] = ['type' => 'input', 'var' => 'amount', 'text' => 'Payment Amount:'];
    $fields[] = ['type' => 'input', 'var' => 'address', 'text' => 'Billing Address:'];
    $fields[] = ['type' => 'input', 'var' => 'address2', 'text' => 'Suite Number (opt):', 'class' => 'cc_address2'];
    $fields[] = ['type' => 'input', 'var' => 'city', 'text' => 'Billing City:', 'class' => 'cc_city'];
    $fields[] = ['type' => 'input', 'var' => 'state', 'text' => 'Billing State:', 'span' => 3, 'class' => 'cc_state'];
    $fields[] = ['type' => 'input', 'var' => 'zip', 'text' => 'Billing Zip:', 'span' => 4, 'class' => 'cc_zip',
      'mask' => '99999'];
    // Credit Card values for Pre-auth.
    $fields[] = ['type' => 'input', 'var' => 'cc_name', 'text' => 'Name on Card:', 'class' => 'cc_name'];
    $fields[] = ['type' => 'input', 'var' => 'cc_number', 'text' => 'Card Number:', 'class' => 'cc_number', 'span' => 5];
    $fields[] = ['type' => 'input', 'var' => 'cc_exp', 'text' => 'Expiration Date:', 'class' => 'cc_exp', 'span' => 2,
      'mask' => '9999'];
    $fields[] = ['type' => 'input', 'var' => 'cc_cvv', 'text' => 'Security Code:', 'class' => 'cc_cvv', 'span' => 2];
    // Check Processing
    $opts = [];
    $opts[] = ['val' => 'C', 'text' => 'Checking'];
    $opts[] = ['val' => 'S', 'text' => 'Savings'];
    $fields[] = ['type' => 'input', 'var' => 'ach_name', 'text' => 'Authorized Billing Contact:', 'class' => 'ach_name',
    'comment' => 'First and Last Name'];
    $fields[] = ['type' => 'select', 'opts' => $opts, 'var' => 'ach_type', 'text' => 'Account Type:', 'class' => 'ach_type'];
    $fields[] = ['type' => 'input', 'var' => 'ach_route', 'text' => 'Routing Number:', 'class' => 'ach_route',
    'comment' => '9 Digit Number, far left side of check', 'mask' => '999999999'];
    $fields[] = ['type' => 'input', 'var' => 'ach_account', 'text' => 'Account Number:', 'class' => 'ach_account', 'comment' => 'Account number after routing number.'];
    $save = Button::init()->text("Authorize Account")->color('success post')->formid('paymentForm')
      ->message("Authorizing...")->icon('money')->render();
    $form = Forms::init()->id('paymentForm')->url("/admin/payments")->elements($fields)->render();
    $data .= $form;
    $data .= $save;
    $data .= BS::encap("
       $('.ach_type_l').hide();
        $('.ach_name_l').hide();
        $('.ach_route_l').hide();
        $('.ach_account_l').hide();

        $('.type').change(function(){
            if ($('.type').val() == 'C')
            {
                $('.ach_type_l').hide();
                $('.ach_route_l').hide();
                $('.ach_account_l').hide();
                $('.ach_name_l').hide();
                $('.cc_name_l').show();
                $('.cc_number_l').show();
                $('.cc_exp_l').show();
                $('.cc_cvv_l').show();
            }
            else
            {
                $('.ach_type_l').show();
                $('.ach_route_l').show();
                $('.ach_account_l').show();
                $('.ach_name_l').show();
                $('.cc_name_l').hide();
                $('.cc_number_l').hide();
                $('.cc_exp_l').hide();
                $('.cc_cvv_l').hide();
            }
            });
        ");
    $data = BS::span(6, $data);
    echo BS::row($data);
