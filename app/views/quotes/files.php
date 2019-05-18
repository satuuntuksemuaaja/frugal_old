<?php

$headers = [null, 'Description', 'Uploaded By', 'Attach to Contractors', 'Delete'];
$rows = [];
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
echo Modal::init()->isInline()->header("Files/Designs")->content($table.$form)->render();

