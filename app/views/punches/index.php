<?php
use Carbon\Carbon;

/**
 * Render progress items.
 * @param $item
 * @return string
 */
function renderProgress($item)
{
    $user = Auth::user();
    // Only allow frugal orders to be able to approve.
    // We have approved - started and completed
    $icons = null;

    // Not approved
    if (!$item->approved && ($user->designation_id == 12 || $user->id == 1 || $user->id == 5 || $user->level_id == 1))
    {
        $icons .= "<a class='get label label-danger tooltiped' data-toggle='tooltip' data-target='#workModal'
                  data-original-title='Not Approved' href='/shopitem/$item->id/approved'>
                  <i class=' fa fa-exclamation'></i></a> &nbsp;&nbsp;";
    }
    // Approved
    if ($item->approved)
    {
        $icons .= "<a class='label label-success tooltiped' data-toggle='tooltip' data-target='#workModal'
                  data-original-title='Approved' href='#'>
                  <i class=' fa fa-exclamation'></i></a> &nbsp;&nbsp;";
    }

    if ($item->approved && !$item->started) // Approved and not started
    {
        $icons .= "<a class='get label label-danger tooltiped' data-toggle='tooltip' data-target='#workModal'
                  data-original-title='Not Started' href='/shopitem/$item->id/started'>
                  <i class=' fa fa-gears'></i></a> &nbsp;&nbsp;";
    }
    if ($item->approved && $item->started)
    {
        $icons .= "<a class='label label-success tooltiped' data-toggle='tooltip' data-target='#workModal'
                  data-original-title='Started' href='#'>
                  <i class=' fa fa-gears'></i></a> &nbsp;&nbsp;";
    }

    if ($item->approved && !$item->completed)
    {
        $icons .= "<a class='get label label-danger tooltiped' data-toggle='tooltip' data-target='#workModal'
                  data-original-title='Not Completed' href='/shopitem/$item->id/completed'>
                  <i class=' fa fa-check'></i></a> &nbsp;&nbsp;";
    }
    if ($item->approved && $item->completed)
    {
        $icons .= "<a class='label label-success tooltiped' data-toggle='tooltip' data-target='#workModal'
                  data-original-title='Completed' href='#'>
                  <i class=' fa fa-check'></i></a> &nbsp;&nbsp;";
    }


    return $icons;

}


/**
 * Check to see if there is any shop work.
 * @param $item
 * @param $rows
 */
function checkShop($item, &$rows)
{
    // if no shop work, just return false. otherwise create new rows.
    \Log::info("Checking $item->id for shop items");
    $shop = Shop::whereJobItemId($item->id)->first();
    if (!$shop) return; // No shop works for this item.
    \Log::info("Shop item exists.");
    foreach ($shop->cabinets AS $cabinet)
    {
        $delete = Auth::user()->id == 1 || Auth::user()->id == 5 ? "<a class='get' href='/shopitem/$cabinet->id/delete'><i class='fa fa-times'></i></a>" : null;
        $rows[] = [
            "color-primary <span class='pull-right'>$delete <b>Shop Work:</b> ". $cabinet->cabinet->cabinet->name . "/" . $cabinet->cabinet->color . "</span>",
            renderProgress($cabinet),
            Editable::init()->id("idDe_id")->placement('bottom')->type('textarea')
                ->title("Notes")->linkText($cabinet->notes ?: "No Notes Found")
                ->url("/shop/$shop->id/$cabinet->id/notes")->render(),
            null,
            null,
            null
        ];
    }


}



/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/1/15
 * Time: 9:06 AM
 */
echo BS::title("{$fft->job->quote->lead->customer->name}", "Punches");
echo Button::init()->icon('download')->url("/fft/$fft->id/signoff/pdf")->color('info btn-sm')->text("Download PDF")->render();

$dbType = ($fft->warranty) ? "Warranty" : "FFT";
if ($fft->warranty)
{
    $dbType = "Warranty";
}
elseif ($fft->service)
{
    $dbType = "Service";
}
else $dbType = "FFT";
$headers = ['Item', 'Status', 'Designation', 'Office Notes', 'Contractor Notes', 'Completed?', 'Created'];
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
        $title = "{$word} received on " . Carbon::parse($item->received)->format('m/d/y');
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
    $i = Editable::init()->id("idMe_$item->id")->placement('right')->type('text')->title("Update List Item")
        ->linkText($item->reference)
        ->url("/item/$item->id/reference")->render();
    $link = $item->poitem ? "<span class='pull-right text-success'><small>Linked to PO: <a href='/po/{$item->poitem->po->id}'>{$item->poitem->po->number}</a> - Ships on: {$item->poitem->po->projected_ship}</small>" : null;

    $notes = Editable::init()->id("idMx_$item->id")->placement('left')->type('textarea')->title("Update Office Notes")
        ->linkText($item->notes)
        ->url("/item/$item->id/notes")->render();
    $contractor_notes = Editable::init()->id("idMx_$item->id")->placement('left')->type('textarea')
        ->title("Contractor Notes")->linkText($item->contractor_notes)
        ->url("/item/$item->id/contractor_notes")->render();
    if ($item->orderable)
    {
        $data .= "<span class='label label-info'>Must be ordered</span>";
    }
    if ($item->replacement)
    {
        $data .= "<span class='label label-warning'>Replacement Part</span>";
    }
    if ($item->image1)
    {
        $data .= "<a class='label label-info' target='_blank' href='/punchimages/$item->image1'><i class='fa fa-image'></i>";
    }
    if ($item->image2)
    {
        $data .= "<a class='label label-info' target='_blank' href='/punchimages/$item->image2'><i class='fa fa-image'></i>";
    }
    if ($item->image3)
    {
        $data .= "<a class='label label-info' target='_blank' href='/punchimages/$item->image3'><i class='fa fa-image'></i>";
    }

    $designation = $item->designation ? $item->designation->name : "None Set";
    $designation = "<a class='mjax' data-toggle='modal' data-target='#workModal'
                  href='/fft_designation/{$item->id}'>$designation</a>";

    $extras = "
    <span class='pull-right'>
       &nbsp; <a class='get' href='/item/$item->id/orderable'><i class='fa fa-dollar'></i></a>
       &nbsp; <a class='get' href='/item/$item->id/replacement'><i class='fa fa-refresh'></i></a>
       &nbsp; <a class='get' href='/item/$item->id/delete'><i class='fa fa-trash-o'></i></a>
       </span>";
    $rows[] = [
        $i . $link .$extras,
        $data,
        $designation,
        $notes,
        $contractor_notes,
        $item->contractor_complete ? "Yes" : "<a class='get' href='/item/$item->id/contractor_complete'>No</a>",
        $item->created_at->format("m/d/y h:i a")
    ];
        checkShop($item, $rows);

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

$opts = [];
$opts[] = ['val' => 'Y', 'text' => 'Send to Shop Work', 'checked' => false];
$fields[] = ['type' => 'checkbox', 'var' => 'shop', 'opts' => $opts, 'text' => null, 'span' => 7];

$fields[] = ['type' => 'file', 'var' => 'image1', 'text' => 'Replacement Image #1', 'comment' => "If this is a replacement part you must upload at least 1 image."];
$fields[] = ['type' => 'file', 'var' => 'image2', 'text' => 'Replacement Image #2'];
$fields[] = ['type' => 'file', 'var' => 'image3', 'text' => 'Replacement Image #3'];
$fields[] = ['type' => 'submit', 'var' => 'submit', 'class' => 'btn-primary', 'val' => 'Save'];
$form = Forms::init()->id('itemForm')->labelSpan(4)->url("/fft/$fft->id/item/create")->elements($fields)->render();
$request = Button::init()->text($fft->warranty ? "Warranty Signoff" : "FFT Signoff")->color('success')->icon('arrow-right')->url("/fft/$fft->id/signoff")->render();
$panel = Panel::init('default')->header("Add Punch")->content($form.$request)->render();

$span = BS::span(12, $table);
echo BS::row($span);
$left = BS::span(6, $panel);
// Set if punches are paid and a textbox for the reason.
$right = null;
if (!$fft->paid)
{
    $right .= "<div class='alert alert-danger'>Punches are currently not marked as paid.</div>";
    $fields = [];
    $opts = [];
    if ($fft->paid)
    {
        $opts[] = ['val' => 1, 'text' => 'Yes'];
        $opts[] = ['val' => 0, 'text' => 'No'];
    }
    else
    {
        $opts[] = ['val' => 0, 'text' => 'No'];
        $opts[] = ['val' => 1, 'text' => 'Yes'];
    }
    $fields[] = ['type' => 'select', 'var' => 'paid', 'text' => 'All Punches Paid?', 'opts' => $opts, 'span' => 7];
    $fields[] = ['type' => 'textarea', 'var' => 'paid_reason', 'text' => 'Notes:', 'span' => 7];

  //  $fields[] = ['type' => 'submit', 'var' => 'submit', 'class' => 'btn-primary', 'val' => 'Update'];
    $save = Button::init()->text("Save")->icon('check')->color('primary post')->formid('payForm')->render();
    $form = Forms::init()->id('payForm')->labelSpan(4)->url("/fft/$fft->id/pay")->elements($fields)->render();
    $panel = Panel::init('default')->header("Payment Status")->content($form)->footer($save)->render();
    $right .= $panel;

}
else
{
    $panel = Panel::init('default')->header("Payment Notes")->content($fft->paid_reason)->footer(null)->render();
    $right .= $panel;

}
$right = BS::span(6, $right);
echo BS::row($left.$right);
echo BS::encap("
$('.editable').editable({
ajaxOptions : {
                type: 'POST',
                dataType: 'json'
                },
      send: 'always'
  });


");
echo Modal::init()->id('workModal')->onlyConstruct()->render();