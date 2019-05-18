<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 12/10/16
 * Time: 12:31 PM
 */
//echo BS::title("Shop Work", null);

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
    if (!$item->approved && ($user->designation_id == 12 || $user->id == 1 || $user->id == 5))
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

$headers = ['Job', 'Entered', 'Cabinet', 'Notes', 'Progress'];
$rows = [];
echo Button::init()->text("Add Shop Work")->icon('plus')->color('primary')->modal('newChange')->render() .
    Button::init()->text("Back to Buildup")->icon('arrow-right')->color('info')->url("/buildup")->render() .

    $data = null;
foreach (Shop::whereActive(true)->get() as $shop)
{
    $rows[] = [
        'color-info ' . $shop->job->quote->lead->customer->name,
        $shop->created_at->format("m/d/y"),
        null,
        null,
        null
    ];
    foreach ($shop->cabinets AS $cabinet)
    {
        $rows[] = [
            null,
            null,
            $cabinet->cabinet->cabinet->name . "/" . $cabinet->cabinet->color,
            Editable::init()->id("idDe_id")->placement('bottom')->type('textarea')
                ->title("Notes")->linkText($cabinet->notes ?: "No Notes Found")
                ->url("/shop/$shop->id/$cabinet->id/notes")->render(),
            renderProgress($cabinet)
        ];
    }
}
$table = Table::init()->headers($headers)->rows($rows);
echo BS::row(BS::span(12, $table));
// Change Modal
$pre = "<h4>Create a new Shop Work Order</h4>";
$fields = [];
$jobs = Job::all();
$opts[] = ['val' => 0, 'text' => '-- Select Job --'];
foreach ($jobs AS $job)
{
    if ($job->quote && $job->quote->type)
    {
        @$opts[] = ['val'  => $job->id,
                    'text' => $job->quote->lead->customer->name . " ({$job->quote->type} - $job->id)"
        ];
    }
}
$fields[] = ['type' => 'select2', 'var' => 'job_id', 'opts' => $opts, 'span' => 6, 'text' => 'Job:', 'width' => 400];
$form = Forms::init()->id('newOrderForm')->labelSpan(4)->elements($fields)->url("/shop/new")->render();
$save = Button::init()->text("Save")->icon('save')->color('primary mpost')->formid('newOrderForm')->render();
echo Modal::init()->id('newChange')->header("New Change Order")->content($pre . $form)->footer($save)->render();

echo BS::encap("
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");
