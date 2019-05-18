<?php

use Carbon\Carbon;


$now = Carbon::now();
echo BS::title("Leads", "Active/Aging Leads");
$headers = ['Customer', 'ID', 'Age', 'Status', 'Source', 'Showroom Scheduled', 'Closing Date', 'Digital Measure', 'Designer'];
$rows = [];
$table = Table::init()->addStyle('leadTable')->id('leadTable')->headers($headers)->rows($rows)->render();
$buttons[] = ['icon' => 'search', 'color' => 'default', 'text' => 'Show All', 'url' => '/leads?showAll=true'];
$buttons[] = ['icon' => 'plus', 'color' => 'primary', 'text' => 'Create Lead', 'modal' => 'newLead'];

$add = BS::Buttons($buttons);
$span = BS::span(12, $add . $table);
echo BS::row($span);


echo "
<script type='text/javascript'>
var loaded = false;
if (!loaded)
{ 
$('.leadTable').DataTable({
    serverSide: true,
    processing : true,
    ajax : '/leads',
    destroy: true,
    stateSave: true
});
loaded = true;
}
</script>
";
// Add Modal
$customers = Customer::orderBy('name', 'ASC')->get();
$opts = [];
$opts[] = ['val' => 0, 'text' => '-- New Customer --'];
foreach ($customers AS $customer)
{
    $opts[] = [
        'val' => $customer->id,
        'text' => $customer->name . ' (' . $customer->city . ", " . $customer->state . ")"
    ];
}
$pre = BS::callout('info', 'When adding a lead, you can either select an existing customer or create a new one. <b>To create a new customer with existing information, select the customer, and add a new name. The rest of the information will copy to the new customer, contact and lead.</b>');
$fields = [];
$fields[] = ['type' => 'select', 'text' => 'Existing Customer:', 'opts' => $opts, 'span' => 6, 'var' => 'customer_id'];
$fields[] = ['type' => 'input', 'text' => 'Name:', 'var' => 'name', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'Address:', 'var' => 'address', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'City:', 'var' => 'city', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'State:', 'var' => 'state', 'span' => 2];
$fields[] = ['type' => 'input', 'text' => 'Zip:', 'var' => 'zip', 'span' => 3, 'mask' => '99999'];
$fields[] = ['type' => 'input', 'text' => 'Job Address:', 'var' => 'job_address', 'span' => 6, 'placeholder' => 'Leave blank if same'];
$fields[] = ['type' => 'input', 'text' => 'Job City:', 'var' => 'job_city', 'span' => 6, 'placeholder' => 'Leave blank if same'];
$fields[] = ['type' => 'input', 'text' => 'Job State:', 'var' => 'job_state', 'span' => 2];
$fields[] = ['type' => 'input', 'text' => 'Job Zip:', 'var' => 'job_zip', 'span' => 3, 'mask' => '99999'];

$fields[] = ['type' => 'input', 'text' => 'E-mail Address:', 'var' => 'email', 'span' => 6];
$fields[] = ['type' => 'input', 'text' => 'Phone:', 'var' => 'home', 'span' => 6, 'mask' => '999.999.9999'];
$fields[] = ['type' => 'input', 'text' => 'Mobile (SMS):', 'var' => 'mobile', 'span' => 6, 'mask' => '999.999.9999'];
$fields[] = [
    'type' => 'input',
    'text' => 'Alternate Phone:',
    'var'  => 'alternate',
    'span' => 6,
    'mask' => '999.999.9999'
];
$sources = LeadSource::orderBy('type', 'ASC')->whereActive(true)->get();
$opts = [];
$opts[] = ['val' => null, 'text' => "-- Select Lead Source -- "];
foreach ($sources AS $source)
{
    $opts[] = ['val' => $source->id, 'text' => $source->type];
}
$fields[] = ['type' => 'select', 'var' => 'source_id', 'opts' => $opts, 'text' => 'Lead Source:', 'span' => 6];
$opts = [];
$users = User::whereLevelId(4)->whereActive(true)->get();
$opts[] = ['val' => null, 'text' => "-- Select Designer -- "];
foreach ($users AS $user)
{
    $opts[] = ['val' => $user->id, 'text' => $user->name];
}
$opts[] = ['val' => 5, 'text' => 'Rich Bishop'];
$fields[] = ['type' => 'select', 'var' => 'user_id', 'opts' => $opts, 'text' => 'Designer:', 'span' => 6];
$opts = [];
$opts[] = ['val' => null, 'text' => '-- Select Location --'];
$opts[] = ['val' => 'Fayetteville', 'text' => "Fayetteville"];
$opts[] = ['val' => 'Roswell', 'text' => "Roswell"];
$opts[] = ['val' => 'Toco Hills', 'text' => "Toco Hills"];
$opts[] = ['val' => 'Acworth', 'text' => "Acworth"];
$opts[] = ['val' => 'Peachtree City', 'text' => "Peachtree City"];

$fields[] = ['type' => 'select', 'var' => 'location', 'opts' => $opts, 'text' => 'Showroom Location:', 'span' => 6];
$form = Forms::init()->id('newCustomerForm')->labelSpan(4)->url("/lead/create")->elements($fields)->render();
$save = Button::init()->text("Add")->color('success mpost')->formid('newCustomerForm')->message("Creating...")
    ->icon('check')->render();



echo Modal::init()->id('newLead')->width('md')->header("Create new Lead")->content($pre . $form)->footer($save)->render();

// Quote Spawn
echo Modal::init()->id('newQuote')->onlyConstruct()->render();
echo Modal::init()->id('workModal')->onlyConstruct()->render();

