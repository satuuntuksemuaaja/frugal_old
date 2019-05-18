<?php
echo BS::title("Granite", $quote->lead->customer->name);
$request = app('request');
if ($request->has('granite_id'))
{
    // Show these granite options
    echo showForm(QuoteGranite::find($request->get('granite_id')), $quote);

}
if ($request->has('new'))
{
    // Show empty form.
    echo showForm(null, $quote);

}

// Show the table.
$headers = ['Location/Description', 'Granite', 'Removal','Measurements', 'BS', 'IS W/L', 'RB L/D'];
$rows = [];
foreach ($quote->granites AS $granite)
{
    $rows[] = [
        "<a href='/quote/$quote->id/granite?granite_id=$granite->id'>$granite->description</a> <span class='pull-right'><a href='/quote/$quote->id/granite?del=$granite->id'><i class='fa fa-trash-o'</a></span>",
        $granite->granite && !$granite->granite_override ? $granite->granite->name : $granite->granite_override,
        $granite->removal_type,
        $granite->measurements,
        $granite->backsplash_height,
        sprintf("%d/%d", $granite->island_width, $granite->island_length),
        sprintf("%d/%d", $granite->raised_bar_length, $granite->raised_bar_depth)
    ];
}
$table = Table::init()->headers($headers)->rows($rows)->render();
$add = Button::init()->text("Add New Granite")->url("/quote/$quote->id/granite?new=true")->color('primary')->icon('plus')->render();
$panel = Panel::init('primary')->header("Granite")->content($table)->footer($add)->render();
echo BS::row(BS::span(12, $panel));



$pass = false;
if ($quote->granites()->count() > 0)
{
    $pass = true;
}
if (!$quote->picking_slab)
{
    echo "<div class='alert alert-danger'>You must select if customer is picking slab or not to continue.</div>";
    $pass = false;
}
    if ($quote->type == 'Cabinet Small Job')
    $pass = true;

$options = "<div class='btn-group'>";
if ($quote->type != 'Granite Only')
{
    $options .= Button::init()
        ->text("Review Cabinets")
        ->color('warning btn-lg')
        ->withoutGroup()
        ->icon('arrow-left')
        ->url("/quote/$quote->id/cabinets")
        ->render();
}


if ($pass)
{
    $options .= Button::init()
        ->text("Next")
        ->color('success btn-lg get')
        ->withoutGroup()
        ->icon('arrow-right')
        ->url("/quote/$quote->id/appliances?moving=true")
        ->render();
}

$options .= Button::init()
    ->text("Quote Overview")
    ->color('info btn-lg')
    ->withoutGroup()
    ->icon('share')
    ->url("/quote/$quote->id/view")
    ->render();
$options .= "</div>";

echo BS::row(BS::span(9, $options, 3));


function showForm(QuoteGranite $g = null, Quote $quote)
{
    // Granite Type
    if (!$g) $g = new QuoteGranite;
    $granites = Granite::orderBy('name', 'ASC')->get();
    $opts = [];
    foreach ($granites AS $granite)
    {
        $opts[] = ['val' => $granite->id, 'text' => $granite->name];
    }

    if ($g->granite_id && $g->granite)
    {
        array_unshift($opts, ['val' => $g->granite_id, 'text' => $g->granite->name]);
    }
    else
    {
        array_unshift($opts, ['val' => 0, 'text' => '-- Select Granite -- ']);
    }
    $fields[] = ['type' => 'input', 'text' => 'Location/Description', 'var' => 'description', 'val' => $g->description, 'span' => 6];
    $fields[] = ['type' => 'select2', 'text' => 'Granite Type:', 'opts' => $opts, 'span' => 6, 'var' => 'granite_id'];
    $opts = [];
    // Is customer picking slab?
    if ($quote->picking_slab)
        $opts[] = ['val' => $quote->picking_slab, 'text' => $quote->picking_slab];
    else
        $opts[] = ['val' => '', 'text' => '-- Select Option --'];
    $opts[] = ['val' => 'Yes', 'text' => 'Yes'];
    $opts[] = ['val' => 'No', 'text' => 'No'];
    $opts[] = ['val' => 'Undecided', 'text' => 'Undecided'];
    $fields[] = ['type' => 'select2', 'text' => 'Customer Picking Slab?:', 'opts' => $opts, 'span' => 6, 'var' => 'picking_slab'];



    // Special granite
    $fields[] = ['type' => 'input', 'text' => "Special Granite?<br/><small><span class='text-danger'>WARNING: This will override granite dropdown</span></small>",
                 'var' => 'granite_override', 'val' => $g->granite_override];
    if ($quote->job)
        $fields[] = ['type' => 'input', 'text' => "** Granite Override **<br/><small><span class='text-danger'>WARNING: This will override the schedule only</span></small>",
                     'var' => 'granite_jo', 'val' => $g->granite_jo, 'comment' => "ONLY CHANGE JOB SCHEDULE", 'span' => 6];


    $fields[] = ['type' => 'input', 'text' => "Special Granite Price p/sqft<br/><small><span class='text-danger'>WARNING: This will override granite dropdown</span></small>",
                 'var' => 'pp_sqft', 'val' => $g->pp_sqft];

// CounterTop Removal Type

    $opts = [];
    if ($g->removal_type)
    {
        $opts[] = ['val' => $g->removal_type, 'text' => $g->removal_type];
    }

    $opts[] = ['val' => '', 'text' => 'No Pre-existing Countertops'];
    $opts[] = ['val' => 'Formica', 'text' => 'Formica'];
    $opts[] = ['val' => 'Corian', 'text' => 'Corian'];
    $opts[] = ['val' => 'Granite', 'text' => 'Granite'];
    $opts[] = ['val' => 'Tile', 'text' => 'Tile'];
    $fields[] = ['type' => 'select', 'text' => 'Countertop Removal Type:', 'var' => 'removal_type',
                 'span' => 6, 'opts' => $opts];

// Countertop Measurements
    $fields[] = ['type' => 'textarea', 'rows' => 6, 'span' => 6,
                 'text'    => 'Counter Measurements: <br/><small><b>one number per line</b></small>',
                 'var'     => 'measurements',
                 'val'     => $g->measurements,
                 'comment' => 'At 25.5 inches'];

// Countertop Edge
    $edges = [];
    if ($g->counter_edge)
    {
        $edges[] = ['val' => $g->counter_edge, 'text' => $g->counter_edge];
    }

    $edges[] = ['val' => '(Standard) Pencil Round - 1/4 Round', 'text' => '(Standard) Pencil Round - 1/4 Round'];
    $edges[] = ['val' => '(Standard) 1/4 Bevel', 'text' => '(Standard) 1/4 Bevel'];
    $edges[] = ['val' => '(Standard) Eased', 'text' => '(Standard) Eased'];
    $edges[] = ['val' => '(Premium) Half Bull Nose ($8/lnft.)', 'text' => '(Premium) Half Bull Nose ($8/lnft.)'];
    $edges[] = ['val' => '(Premium) Half Bevel ($8/lnft.)', 'text' => '(Premium) Half Bevel ($8/lnft.)'];
    $edges[] = ['val' => '(Premium) Full Bull Nose ($12/lnft.)', 'text' => '(Premium) Full Bull Nose ($12/lnft.)'];
    $edges[] = ['val' => '(Premium) 2cm Ogee ($14/lnft.)', 'text' => '(Premium) 2cm Ogee ($14/lnft.)'];
    $edges[] = ['val' => '(Premium) French Ogee ($20/lnft.)', 'text' => '(Premium) French Ogee ($20/lnft.)'];
    $edges[] = ['val' => '(Premium) Dupont ($24/lnft.)', 'text' => '(Premium) Dupont ($24/lnft.)'];
    $edges[] = ['val' => '(Premium) Demi Bullnose ($5/lnft.)', 'text' => '(Premium) Demi Bullnose ($5/lnft.)'];

    $fields[] = ['type' => 'select', 'span' => 4, 'text' => 'Counter Edge:', 'var' => 'counter_edge', 'opts' => $edges, 'span' => 6];

    $fields[] = ['type' => 'input',
                 'text' => 'Countertop Edge in Linear Ft. <br/><b>(if premium)</b><br/><small>Leave blank if a standard edge is used.</small>',
                 'var'  => 'counter_edge_ft', 'val'  => $g->counter_edge_ft];

// Backsplash primaryrmation
    $fields[] = ['span' => 2, 'type' => 'input', 'text' => 'Backsplash Height in Inches:<br/><small>Leave 0 if no backsplash</small>',
                 'var' => 'backsplash_height', 'val' => $g->backsplash_height];
    $fields[] = ['span' => 2, 'type' => 'input', 'text' => 'Raised Bar Countertop Length:', 'var' => 'raised_bar_length',
                 'val' => $g->raised_bar_length];
    $fields[] = ['span' => 2, 'type' => 'input', 'text' => 'Raised Bar Countertop Depth:', 'var' => 'raised_bar_depth',
                 'val' => $g->raised_bar_depth];
    $fields[] = ['span' => 2, 'type' => 'input', 'text' => 'Island Granite (width):', 'var' => 'island_width',
                 'val' => $g->island_width];
    $fields[] = ['span' => 2, 'type' => 'input', 'text' => 'Island Granite (length):', 'var' => 'island_length',
                 'val' => $g->island_length];
    $fields[] = ['type' => 'submit', 'var' => 'updateGranite', 'val' => 'Update Granite Requirements', 'class' => 'btn-danger pulse-red'];
    $fields[] = ['type' => 'hidden', 'var' => 'g_id', 'val' => $g->id];
    $form = Forms::init()->id('primaryForm')->labelSpan(4)->elements($fields)->url("/quote/$quote->id/granite?update=yes")->render();
    $panel = Panel::init('primary')->header("Granite Information")->content($form)->render();
    $left  = BS::span(6, $panel);
    return BS::row($left);
}