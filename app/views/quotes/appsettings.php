<?php

$headers = ["Appliance", 'Brand', 'Model', 'Size'];
$rows = [];
$meta = unserialize($quote->meta)['meta'];
$appids = (isset($meta['quote_appliances'])) ? $meta['quote_appliances'] : [];

// First check and make sure we have the records written.
foreach ($appids as $app)
{
    if ($quote->appliances()->whereApplianceId($app)->count() == 0)
        (new QuoteAppliance)->create([
           'quote_id' => $quote->id,
           'appliance_id' => $app
        ]);
}

$fields = [];
foreach ($quote->appliances AS $app)
{
    $rows[] = [
        $app->appliance->name,
        "<input name='app_{$app->id}_brand' value='$app->brand'>",
        "<input name='app_{$app->id}_model' value='$app->model'>",
        "<input name='app_{$app->id}_size' value='$app->size'>",
    ];
}
$start = "<form action='/quote/$quote->id/appsettings' id='appform' method='post'>";
$table = Table::init()->headers($headers)->rows($rows)->render();
$end = "</form>";
// Get a list of appliances.
/*


foreach ($quote->files AS $file)
{
    $rows[] = ["<a href='/files/$quote->id/$file->location'><i class='fa fa-download'></i></a>",
               $file->description,
               $file->user->name . " on " . $file->created_at,
               $file->attached ? "<a class='get' href='/file/{$file->id}/attach'>Yes</a>" :
                   "<a class='get' href='/file/{$file->id}/attach'>No</a>",
               "<a class='get' href='/file/{$file->id}/delete'><i class='fa fa-trash-o'></i></a>"];
}
$table = Table::init()->headers($headers)->rows($rows)->render();

// Add
if (Auth::user()->level_id != 6)
{
    $fields = [];
    $fields[] = ['type' => 'input', 'var' => 'description', 'text' => 'File Description:', 'span' => 5];
    $fields[] = ['type' => 'file', 'var' => 'frugalFile', 'text' => 'Select File:'];
    $fields[] = ['type' => 'hidden', 'var' => 'redirect', 'val' => "/quote/$quote->id/view"];
    $fields[] = ['type' => 'submit', 'class' => 'btn-primary', 'var' => 'submit', 'val' => 'Upload File'];
    $form = Forms::init()->id('frugalUploader')->labelSpan(4)->url("/quote/$quote->id/files/upload")->elements($fields)->render();
}

else $form = null;
*/
$form = $start.$table.$end;
$save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('appform')->render();
$send = Button::init()->text("Send Appliances to Customer")->icon('send')->color('info get')->url("/quote/$quote->id/appsettings/send")->render();
echo Modal::init()->isInline()->header("Appliance Settings")->content($form)->footer($save . $send)->render();

