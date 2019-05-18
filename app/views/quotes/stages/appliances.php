<?php
echo BS::title("Appliances", $quote->lead->customer->name);
$meta = unserialize($quote->meta);
$meta = $meta['meta'];
if ( ! isset($meta['sinks']))
{
	$meta['sinks'] = [];
}

// Panel 1 - Sink Configuration
$headers = ['Sink', 'Delete'];
$rows = [];

foreach ($meta['sinks'] AS $idx => $sink)
{
	if ($sink)
	{
		if ( ! isset($meta['sink_plumber']))
		{
			$meta['sink_plumber'] = [];
		}

		$plumber = (in_array($sink, $meta['sink_plumber'])) ? "<span class='pull-right'><i class='fa fa-check'></i></span>" : null;
		$rows[] = [Sink::find($sink)->name.$plumber,
			"<a class='get' href='/quote/$quote->id/sink/$idx/remove'><i class='fa fa-trash-o'></i></a>"];
	}
}

$sinkTable = Table::init()->headers($headers)->rows($rows)->render();
$add = Button::init()->text("Add Sink")->icon('plus')->modal('sinkModal')->color('danger pulse-red')->render();
$sinkPanel = Panel::init('primary')->header("Sink Requirements")->content($sinkTable)->footer("<center>$add</center>")->render();

// Appliances
if ($quote->type != 'Granite Only')
{
	$headers = [null, 'Appliance', 'Additional Cost'];
	$applianceStore = (isset($meta['quote_appliances'])) ? $meta['quote_appliances'] : [];
	//dd($applianceStore);
	$rows = [];
	$form = "<form id='applianceForm' method='post' action='/quote/$quote->id/appliances'>";
	$appliances = Appliance::orderBy('name', 'ASC')->get();
	foreach ($appliances AS $appliance)
	{
		$checked = (in_array($appliance->id, $applianceStore)) ? 'checked' : null;
		$rows[] = ["<input type='checkbox' name='app_$appliance->id' value='Y' $checked>",
			$appliance->name,
			"$".number_format($appliance->price, 2),
		];
	}
	$table = $form.Table::init()->id('applianceTable')->headers($headers)->rows($rows)->dataTables()->render();
	$save = "<input type='hidden' name='appliances' value='Y'>".Button::init()->text("Save Appliances")->color('danger post pulse-red')
	                                                                            ->formid('applianceForm')->icon('save')->render();
	$appliancePanel = Panel::init('info')->header("Appliances")->content($table)->footer("<center>$save</center></form>")->render();
}
else
{
	$appliancePanel = null;
}

$left  = BS::span(6, $sinkPanel);
$right = BS::span(6, $appliancePanel);

echo BS::row($left.$right);

$pass = true;
if ( ! isset($meta['progress_appliance']))
{
	$pass = false;
}

$options = "<div class='btn-group'>";
$options .= Button::init()->text("Review Granite")->color('warning btn-lg')->withoutGroup()->icon('arrow-left')
                          ->url("/quote/$quote->id/granite")->render();

if ($pass)
{
	$options .= Button::init()->text("Next")->color('success btn-lg ')->withoutGroup()->icon('arrow-right')
	                          ->url("/quote/$quote->id/accessories")->render();
}

$options .= Button::init()->text("Quote Overview")->color('info btn-lg')->withoutGroup()->icon('share')
                          ->url("/quote/$quote->id/view")->render();
$options .= "</div>";

echo BS::row(BS::span(9, $options, 3));

$obj = \vl\quotes\QuoteGenerator::getQuoteObject($quote);
if (isset($obj->GTTL))
{
	if ($quote->type == 'Full Kitchen' && $obj->GTTL < 50)
	{
		echo BS::encap("alert('Warning: Granite Square Footage was less than 50 ($obj->GTTL). If this is incorrect, please click back and adjust counter measurements');");
	}
}

// Sink Modal
$fields = [];
$sinks  = Sink::orderBy('name', 'ASC')->get();
$opts   = [];
foreach ($sinks AS $sink)
{
	$opts[] = ['val' => $sink->id, 'text' => $sink->name];
}

$fields[] = ['type' => 'select2', 'var' => 'sink_id', 'opts' => $opts, 'text' => 'Sink Type:'];
if ($quote->type == 'Cabinet Small Job')
{
	$opts = [['val' => 'Y', 'text' => 'Will a plumber be needed to install this sink?']];
	$fields[] = ['type' => 'checkbox', 'var' => 'plumber_needed', 'opts' => $opts, 'span' => 7];
}
$form = Forms::init()->id('sinkForm')->elements($fields)->url("/quote/$quote->id/sinks/add")->render();
$save = Button::init()->text("Add")->color('primary mpost')->formid('sinkForm')->icon('plus')->render();
echo Modal::init()->id('sinkModal')->header("Add Sink to Quote")->content($form)->footer($save)->render();