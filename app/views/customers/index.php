<?php
use vl\core\Formatter;
echo BS::title("Customers", "My Customers");
$headers = ['Name', 'Address', 'Contact', 'E-mail', 'Number'];
$rows = [];
foreach ($customers AS $customer)
  $rows[] = ["<a href='/profile/$customer->id/view'>$customer->name</a>",
      $customer->address . " " . $customer->city. ", " . $customer->state . " " . $customer->zip,
      $customer->contacts->first()->name,
      $customer->contacts->first()->email,
      Formatter::numberFormat($customer->contacts->first()->home),
      ];
$add = "<span class='pull-right'>".
        Button::init()->text("Add Customer")->color('primary btn-xs')->modal('newCustomer')->icon('plus')->render()."</span>";
$table = Table::init()->headers($headers)->rows($rows)->dataTables()->render();
$panel = Panel::init('default')->header("Customer List", $add)->content($table)->render();
$left = BS::span(8, $panel);
echo BS::row($left);

// Add Modal
$pre = BS::callout('info', 'When adding a customer, you will also be creating a contact record for the company. You can have multiple
contacts for a customer.');
$fields = [];
$fields[] = ['type' => 'input', 'text' => 'Name:', 'var' => 'name', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'Address:', 'var' => 'address', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'City:', 'var' => 'city', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'State:', 'var' => 'state', 'span' => 2];
$fields[] = ['type' => 'input', 'text' => 'Zip:', 'var' => 'name', 'span' => 3, 'mask' => '99999'];
$fields[] = ['type' => 'input', 'text' => 'E-mail Address:', 'var' => 'email', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'Phone:', 'var' => 'home', 'span' => 6, 'mask' => '999-999-9999'];
$fields[] = ['type' => 'input', 'text' => 'Mobile (SMS):', 'var' => 'mobile', 'span' => 6, 'mask' => '999-999-9999'];
$fields[] = ['type' => 'input', 'text' => 'Alternate Phone:', 'var' => 'alternate', 'span' => 6, 'mask' => '999-999-9999'];
$form = Forms::init()->id('newCustomerForm')->labelSpan(4)->url("/customers/create")->elements($fields)->render();
$save = Button::init()->text("Add")->color('success mpost')->formid('newCustomerForm')->message("Creating...")
  ->icon('check')->render();
echo Modal::init()->id('newCustomer')->width('md')->header("Create new Customer")->content($pre.$form)->footer($save)->render();