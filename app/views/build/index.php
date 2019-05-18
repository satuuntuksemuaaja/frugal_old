<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/24/15
 * Time: 9:03 AM
 */
use Carbon\Carbon;
use vl\facades\bootstrap\BS;
use vl\libraries\bootstrap\Button;
use vl\libraries\bootstrap\Table;



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


echo BS::title("Build Status", "Buildup Manager");
$headers = ['Job', 'Sold', 'Starts on', 'Cabinets', 'Built Up', 'Load Status', 'Notes'];
$rows = [];
foreach (Job::where('start_date', '!=', '0000-00-00')->orderBy('start_date', 'ASC')->get() as $job)
{
    if (!$job->quote) continue;
    if (!$job->quote->lead) continue;
    if (!$job->quote->lead->customer) continue;
    if ($job->built && $job->loaded) continue;
    $start = Carbon::parse($job->start_date);
    $cabinets = null;
    foreach ($job->quote->cabinets AS $cabinet)
    {
        $cabinets[] = "<b>". $cabinet->cabinet->name . "</b> ({$cabinet->cabinet->vendor->name})";
    }
    $cabinets = implode(", ", $cabinets);
    $buildColor = $start->diffInDays() <= 4 ? 'danger' : 'info';
    $loadColor = $start->diffInDays() <= 2 ? 'danger' : 'info';
    $buildControl = Button::init()->text("Build")->color($buildColor . ' get btn-xs')->icon('arrow-up')->url("/build/$job->id/build")->render();
    $leftControl = Button::init()->text("Loaded Awaiting Departure")->color('warning get')->icon('arrow-right')->url("/build/$job->id/left")->render();
    $loadControl = Button::init()->text("Load on Truck")->color($loadColor . ' get btn-xs')->icon('fa fa-truck')->url("/build/$job->id/load")->render();
    if ($job->loaded && !$job->truck_left)
    {
        $load = $leftControl;
    }
    elseif (!$job->loaded)
    {
        $load = $loadControl;
    }
    else
    {
        $load = "<a class='btn btn-success'><i class='fa fa-check'></i></a>";
    }
    $notes = "<span class='pull-right'><a class='tooltiped mjax' data-toggle='tooltip' data-placement='left'
                data-original-title='Add Note' data-toggle='modal' data-target='#workModal'
                href='/job/{$job->id}/buildupnote'><i class='fa fa-plus'></i></a></span>";
    foreach ($job->buildnotes AS $note)
    {
        $notes .= "<b>". $note->created_at->format("m/d/y h:i a") . ' ' . $note->user->name . ' - </b> ' . nl2br($note->note). "<br/><br/>";
    }
    $drawings = "<a class='tooltiped mjax' data-toggle='tooltip' data-placement='right'
                data-original-title='Drawings' data-toggle='modal' data-target='#workModal'
                href='/quote/{$job->quote->id}/files'><i class='fa fa-image'></i></a>";
    $rows[] = [
        "[".$job->quote->lead->customer->id. "] ". $job->quote->lead->customer->name . "<span class='pull-right'>$drawings</span>",
        $job->created_at->format("m/d/y"),
        $start->format('m/d/y'),
        $cabinets,
        $job->built ? "Yes" : $buildControl,
        $load,
        $notes
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->addStyle('table-condensed table-striped')->render();
$rec = \vl\facades\bootstrap\Button::init()->text("Go to Receiving")->icon('arrow-right')->url("/receiving")->color('warning btn-lg')->render();
$color = Shop::whereActive(true)->count() > 0 ? 'danger' : 'success';
$rec .= \vl\facades\bootstrap\Button::init()->text("Shop Work")->icon('wrench')->url("/shop")->color($color . ' btn-lg')->render();

$auto = !Input::has('refresh')
    ? Button::init()->text('Start Refresh (30 sec)')->color('info btn-lg')->icon('refresh')->url("/buildup?refresh=true")->render()
    : Button::init()->text('Cancel Refresh')->color('info btn-lg')->icon('refresh')->url("/buildup")->render();

echo BS::row(BS::span(12, $rec.$auto));
$span = BS::span(7, $table);

$headers = ['Job',  'Cabinet', 'Notes', 'Status'];
$rows = [];
echo Button::init()->text("Add Shop Work")->icon('plus')->color('primary')->modal('newChange')->render() .
    Button::init()->text("Back to Buildup")->icon('arrow-right')->color('info')->url("/buildup")->render() .

    $data = null;
foreach (Shop::whereActive(true)->get() as $shop)
{
    if (!$shop->job)
    {
        $shop->delete();
        continue;
    }
    if ($shop->job_item_id && !$shop->jobitem)
    {
        $shop->delete();
    }
    $punch = $shop->job_item_id ? "<small>From Punch Item: {$shop->jobitem->reference}</small>" : null;
    $rows[] = [
        'color-info ' . $shop->job->quote->lead->customer->name . "<br/><small>".$shop->created_at->format("m/d/y")."</small>",

        null,
        $punch,
        null
        ];
    foreach ($shop->cabinets AS $cabinet)
    {
        $status = "<span class='text-danger'>Pending Approval</span>";
        if ($cabinet->approved)
            $status = "<span class='text-info'>Approved</span>";
        if ($cabinet->started)
            $status = "<span class='text-success'>Started Work</span>";
        if ($cabinet->complete)
            $status = "<span class='text-success'><b>Completed</b></span>";
        $rows[] = [
            null,

            $cabinet->cabinet->cabinet->name . "/" . $cabinet->cabinet->color,
            Editable::init()->id("idDe_id")->placement('bottom')->type('textarea')
                ->title("Notes")->linkText($cabinet->notes ?: "No Notes Found")
                ->url("/shop/$shop->id/$cabinet->id/notes")->render(),
            renderProgress($cabinet)
        ];
    }
}
$shoptable = Table::init()->headers($headers)->rows($rows);
$span .= BS::span(5, $shoptable);

// Change Modal
$pre = "<h4>Create a new Shop Work Order</h4>";
$fields = [];
$jobs = Job::all();
$opts[] = ['val' => 0, 'text' => '-- Select Job --'];
foreach ($jobs AS $job)
    if ($job->quote && $job->quote->type)
        @$opts[] = ['val' => $job->id, 'text' => $job->quote->lead->customer->name . " ({$job->quote->type} - $job->id)"];
$fields[] = ['type' => 'select2', 'var' => 'job_id', 'opts' => $opts, 'span' => 6, 'text' => 'Job:', 'width' => 400];
$form = Forms::init()->id('newOrderForm')->labelSpan(4)->elements($fields)->url("/shop/new")->render();
$save = Button::init()->text("Save")->icon('save')->color('primary mpost')->formid('newOrderForm')->render();
echo Modal::init()->id('newChange')->header("New Change Order")->content($pre . $form)->footer($save)->render();

echo BS::encap("
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");



echo BS::row($span);

echo BS::encap("
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  ");
if (Input::has('refresh'))
{
    echo "<meta http-equiv='refresh' content='30' >";
}
echo Modal::init()->onlyConstruct()->id('workModal')->render();