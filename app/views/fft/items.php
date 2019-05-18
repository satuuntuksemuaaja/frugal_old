<?php
use Carbon\Carbon;


$dbType = ($fft->warranty) ? "Warranty" : "FFT";
if ($fft->service)
    $dbType = "Service";
if ($fft->service)
    $dbType = "Service";
$headers = ['Item', 'Status', 'Notes', 'Created'];
$rows = [];
foreach ($fft->job->items()->whereInstanceof($dbType)->get() AS $item)
{
    $data = null;
    $word = "Item(s)";
    if ($item->ordered != '0000-00-00')
    {
        $title = "{$word} ordered on " . Carbon::parse($item->ordered)->format('m/d/y');
        $color = 'success';
        $type = 'fa-cloud-upload';

    }
    else
    {
        $title = "{$word} not ordered.";
        $color = 'danger';
        $type = 'fa-cloud-upload';
    }
    if ($item->orderable)
    {
        $data .= "<a class='get label label-$color tooltiped' data-toggle='tooltip'
                  data-original-title='$title' href='/fft/$fft->id/item/$item->id/update'>
                  <i class='text-{$color} fa {$type}'></i></a>";
    }

    if ($item->confirmed != '0000-00-00')
    {
        $title = "{$word} confirmed on " . Carbon::parse($item->ordered)->format('m/d/y');
        $color = 'success';
        $type = 'fa-check-square-o';
    }
    else
    {
        $title = "{$word} not confirmed";
        $color = 'danger';
        $type = 'fa-check-square-o';
    }
    if ($item->orderable)
    {
        $data .= "<a class='label label-$color get tooltiped' data-toggle='tooltip'
                data-original-title='$title' href='/fft/$fft->id/item/$item->id/update'>
                <i class='text-{$color} fa {$type}'></i></a>";
    }

    if ($item->received != '0000-00-00')
    {
        $title = "{$word} received on " . Carbon::parse($item->ordered)->format('m/d/y');
        $color = 'success';
        $type = 'fa-arrow-down';
    }
    else
    {
        $title = "{$word} not received";
        $color = 'danger';
        $type = 'fa-arrow-down';
    }
    if ($item->orderable)
    {
        $data .= "<a class='label label-$color get tooltiped' data-toggle='tooltip'
                data-original-title='$title' href='/fft/$fft->id/item/$item->id/update'>
              <i class='text-{$color} fa {$type}'></i></a>";
    }


    if ($item->verified != '0000-00-00')
    {
        $title = "{$word} verified on " . Carbon::parse($item->ordered)->format('m/d/y');
        $color = 'success';
        $type = 'fa-check';
    }
    else
    {
        $title = "{$word} not verified";
        $color = 'danger';
        $type = 'fa-check';

    }
    $data .= "<a class='label label-$color get tooltiped' data-toggle='tooltip'
                data-original-title='$title' href='/fft/$fft->id/item/$item->id/update'>
                <i class='text-{$color} fa {$type}'></i></a>";
    $i = Editable::init()->id("idMe_$item->id")->placement('right')->type('text')->title("Update List Item")->linkText($item->reference)
        ->url("/item/$item->id/reference")->render();
    $notes = Editable::init()->id("idMx_$item->id")->placement('left')->type('textarea')->title("Update Notes")->linkText($item->notes)
        ->url("/item/$item->id/notes")->render();
    if ($item->orderable)
        $data .= "<span class='label label-info'>Must be ordered</span>";
    if ($item->replacement)
        $data .= "<span class='label label-warning'>Replacement Part</span>";

    $extras = "
    <span class='pull-right'>
       &nbsp; <a class='get' href='/item/$item->id/orderable'><i class='fa fa-dollar'></i></a>
       &nbsp; <a class='get' href='/item/$item->id/replacement'><i class='fa fa-refresh'></i></a>
       &nbsp; <a class='get' href='/item/$item->id/delete'><i class='fa fa-trash-o'></i></a>
       </span>";

    // If there is shop work for this item, let's try to build it underneath this

    $rows[] = [
        $i . $extras,
        $data,
        $notes,
        $item->created_at->format("m/d/y h:i a")
    ];

}
$type = ($fft->warranty) ? "Warranty" : "Punch List";




$table = Table::init()->headers($headers)->rows($rows)->render();

$fields = [];
$fields[] = ['type' => 'input', 'text' => "New $type Item", 'var' => 'item', 'span' => 7];
$opts[] = ['val' => 'Y', 'text' => 'Item must be ordered', 'checked' => true];
$fields[] = ['type' => 'checkbox', 'var' => 'orderable', 'opts' => $opts, 'text' => null, 'span' => 7];
$opts = [];
$opts[] = ['val' => 'Y', 'text' => 'Item is a replacement part', 'checked' => false];
$fields[] = ['type' => 'checkbox', 'var' => 'replacement', 'opts' => $opts, 'text' => null, 'span' => 7];
$form = Forms::init()->id('itemForm')->labelSpan(4)->url("/fft/$fft->id/item/create")->elements($fields)->render();
$save = Button::init()->text("Create {$type} Item")->color('info mpost')->formid('itemForm')->icon('edit')->render();
$request = Button::init()->text("FFT Signoff")->color('success')->icon('arrow-right')->url("/fft/$fft->id/signoff")->render();
echo Modal::init()->isInline()->header("{$type} Items")->content($table . $form)->footer($save . $request)->withClose()->render();
echo BS::encap("
$('.editable').editable({
ajaxOptions : {
                type: 'POST',
                dataType: 'json'
                },
      send: 'always'
  });


");
